<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Repositories\WebsiteRepository;
use ShieldLayer\Services\SecurityScannerService;
use ShieldLayer\Middleware\CsrfMiddleware;
use ShieldLayer\Support\Security;

class WebsiteController
{
    private WebsiteRepository $websiteRepo;

    public function __construct()
    {
        $this->websiteRepo = new WebsiteRepository();
    }

    public function store(Request $request): void
    {
        CsrfMiddleware::handle($request);
        Security::startSession();

        $tenantId = $_SESSION['active_tenant_id'] ?? null;
        if (!$tenantId) {
            Response::redirect('/dashboard');
        }

        $rawUrl = trim($request->input('url', ''));
        if (empty($rawUrl)) {
            $_SESSION['flash_error'] = 'Please enter a valid website URL.';
            Response::redirect('/dashboard');
        }

        if (!preg_match('#^https?://#i', $rawUrl)) {
            $rawUrl = 'https://' . $rawUrl;
        }

        $parsed = parse_url($rawUrl);
        $domain = strtolower($parsed['host'] ?? '');

        // Prevent public platforms like Facebook, YouTube, Google
        $disallowedDomains = ['facebook.com', 'www.facebook.com', 'google.com', 'youtube.com', 'twitter.com', 'x.com', 'instagram.com', 'linkedin.com'];
        if (in_array($domain, $disallowedDomains)) {
            $_SESSION['flash_error'] = 'You can only add websites you own (e.g. your-company.com), not public social profiles.';
            Response::redirect('/dashboard');
        }

        if (empty($domain)) {
            $_SESSION['flash_error'] = 'Invalid domain structure.';
            Response::redirect('/dashboard');
        }

        $this->websiteRepo->create($tenantId, $domain, $rawUrl);
        $_SESSION['flash_success'] = "Website {$domain} added. Please verify ownership to proceed.";
        Response::redirect('/dashboard');
    }

    public function verify(Request $request): void
    {
        CsrfMiddleware::handle($request);
        Security::startSession();

        $id = $request->input('website_id', '');
        $site = $this->websiteRepo->findById($id);

        if (!$site) {
            $_SESSION['flash_error'] = 'Website record not found.';
            Response::redirect('/dashboard');
        }

        $domain = $site['domain'];
        $expectedToken = $site['verification_token'];
        $verified = false;

        // Method 1: Safe DNS TXT check
        $dnsRecords = @dns_get_record($domain, DNS_TXT);
        if ($dnsRecords) {
            foreach ($dnsRecords as $rec) {
                if (!empty($rec['txt']) && strpos($rec['txt'], $expectedToken) !== false) {
                    $verified = true;
                    break;
                }
            }
        }

        // Method 2: Safe HTTP File verification fallback
        if (!$verified) {
            $checkUrl = "http://{$domain}/.well-known/shieldlayer-verification.txt";
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'follow_location' => 1]]);
            $body = @file_get_contents($checkUrl, false, $ctx);
            if ($body && strpos(trim($body), $expectedToken) !== false) {
                $verified = true;
            }
        }

        if ($verified) {
            $this->websiteRepo->markVerified($id);
            $_SESSION['flash_success'] = "Ownership verified for {$domain}! Website is now Protection Ready.";
        } else {
            $_SESSION['flash_error'] = "Ownership check failed for {$domain}. Make sure the DNS TXT record or verification file is accessible.";
        }

        Response::redirect('/dashboard');
    }

    public function triggerScan(Request $request): void
    {
        CsrfMiddleware::handle($request);
        Security::startSession();

        $id = $request->input('website_id', '');
        $site = $this->websiteRepo->findById($id);

        if (!$site) {
            $_SESSION['flash_error'] = 'Website not found for scan.';
            Response::redirect('/dashboard');
        }

        $scanner = new SecurityScannerService();
        $result = $scanner->scan($site['id'], $site['domain'], $site['origin_url']);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Scan completed for {$site['domain']}! Security Score: {$result['score']}/100.";
        } else {
            $_SESSION['flash_error'] = "Scan failed: " . ($result['error'] ?? 'Unknown error');
        }

        Response::redirect('/dashboard');
    }

    public function verifyTraffic(Request $request): void
    {
        CsrfMiddleware::handle($request);
        Security::startSession();

        $id = $request->input('website_id', '');
        $site = $this->websiteRepo->findById($id);

        if (!$site) {
            $_SESSION['flash_error'] = 'Website not found.';
            Response::redirect('/dashboard');
        }

        $targetUrl = preg_match('#^https?://#i', $site['origin_url']) ? $site['origin_url'] : "https://{$site['domain']}";

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true,
                'header' => [
                    "X-ShieldLayer-Probe: true",
                    "X-ShieldLayer-Token: {$site['verification_token']}"
                ]
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);

        $resHeaders = @get_headers($targetUrl, true, $ctx);
        $trafficPassing = false;

        if ($resHeaders) {
            foreach ($resHeaders as $k => $v) {
                if (strtolower($k) === 'x-shieldlayer-active' || strtolower($k) === 'cf-ray' || strtolower($k) === 'x-forwarded-by-shieldlayer') {
                    $trafficPassing = true;
                    break;
                }
            }
        }

        $db = \ShieldLayer\Core\Database::getConnection();

        if ($trafficPassing) {
            $stmt = $db->prepare("UPDATE websites SET status = 'PROTECTED', traffic_verified_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_success'] = "Traffic verified! ShieldLayer is now actively protecting {$site['domain']}.";
        } else {
            $stmt = $db->prepare("UPDATE websites SET status = 'PROTECTION_READY' WHERE id = :id AND status != 'PROTECTED'");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_error'] = "Traffic verification pending: ShieldLayer proxy header was not detected on {$site['domain']}.";
        }

        Response::redirect('/dashboard');
    }
}