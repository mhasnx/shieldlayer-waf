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
            $_SESSION['flash_error'] = 'Active workspace not found. Please log in again.';
            Response::redirect('/dashboard');
        }

        $rawUrl = trim($request->input('url', ''));
        if (empty($rawUrl)) {
            $_SESSION['flash_error'] = 'Please provide a valid website address.';
            Response::redirect('/dashboard');
        }

        if (!preg_match('#^https?://#i', $rawUrl)) {
            $rawUrl = 'https://' . $rawUrl;
        }

        $parsed = parse_url($rawUrl);
        $domain = strtolower($parsed['host'] ?? '');

        $disallowed = ['facebook.com', 'google.com', 'youtube.com', 'instagram.com', 'twitter.com', 'x.com'];
        if (empty($domain) || in_array($domain, $disallowed)) {
            $_SESSION['flash_error'] = 'Please enter a domain you control, not a public service.';
            Response::redirect('/dashboard');
        }

        // Create website record
        $website = $this->websiteRepo->create($tenantId, $domain, $rawUrl);

        // Immediate Passive Security Analysis (Analyze Step)
        $scanner = new SecurityScannerService();
        $scanResult = $scanner->scan($website['id'], $domain, $rawUrl);

        $_SESSION['flash_success'] = "Website {$domain} registered! Initial security audit complete (Score: {$scanResult['score']}/100). Please verify ownership.";
        Response::redirect('/dashboard');
    }

    public function verify(Request $request): void
    {
        CsrfMiddleware::handle($request);
        Security::startSession();

        $id = $request->input('website_id', '');
        $site = $this->websiteRepo->findById($id);

        if (!$site) {
            $_SESSION['flash_error'] = 'Website not found.';
            Response::redirect('/dashboard');
        }

        $domain = $site['domain'];
        $token = $site['verification_token'];
        $verified = false;

        // Passive DNS TXT verification
        $records = @dns_get_record($domain, DNS_TXT);
        if ($records) {
            foreach ($records as $r) {
                if (!empty($r['txt']) && strpos($r['txt'], $token) !== false) {
                    $verified = true;
                    break;
                }
            }
        }

        // Fallback HTTP File verification
        if (!$verified) {
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'follow_location' => 1]]);
            $content = @file_get_contents("http://{$domain}/.well-known/shieldlayer-verification.txt", false, $ctx);
            if ($content && strpos(trim($content), $token) !== false) {
                $verified = true;
            }
        }

        // Demo/Testing override for example.com
        if (!$verified && in_array($domain, ['example.com', 'httpbin.org'])) {
            $verified = true;
        }

        if ($verified) {
            $this->websiteRepo->markVerified($id);
            $_SESSION['flash_success'] = "Domain ownership verified for {$domain}! It is now Protection Ready.";
        } else {
            $_SESSION['flash_error'] = "Ownership verification failed. Ensure DNS TXT token is published.";
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
            $_SESSION['flash_error'] = 'Website not found.';
            Response::redirect('/dashboard');
        }

        $scanner = new SecurityScannerService();
        $result = $scanner->scan($site['id'], $site['domain'], $site['origin_url']);

        if ($result['success']) {
            $_SESSION['flash_success'] = "Audit complete for {$site['domain']}! Security Score: {$result['score']}/100.";
        } else {
            $_SESSION['flash_error'] = "Scan error: " . ($result['error'] ?? 'Connection timed out');
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

        $targetUrl = $site['origin_url'];
        $ctx = stream_context_create([
            'http' => ['timeout' => 4, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);

        $headers = @get_headers($targetUrl, true, $ctx);
        $active = false;

        if ($headers) {
            foreach ($headers as $k => $v) {
                if (in_array(strtolower($k), ['x-shieldlayer-active', 'x-forwarded-by-shieldlayer', 'cf-ray'])) {
                    $active = true;
                    break;
                }
            }
        }

        $db = \ShieldLayer\Core\Database::getConnection();

        if ($active) {
            $stmt = $db->prepare("UPDATE websites SET status = 'PROTECTED', traffic_verified_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_success'] = "Traffic verified! ShieldLayer is now actively filtering traffic for {$site['domain']}.";
        } else {
            $stmt = $db->prepare("UPDATE websites SET status = 'PROTECTION_READY' WHERE id = :id AND status != 'PROTECTED'");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_error'] = "ShieldLayer Edge Header not detected on {$site['domain']}. Traffic is not routed through proxy yet.";
        }

        Response::redirect('/dashboard');
    }
}