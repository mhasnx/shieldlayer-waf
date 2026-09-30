<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Repositories\WebsiteRepository;
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

        if (empty($domain) || !filter_var(gethostbyname($domain), FILTER_VALIDATE_IP)) {
            // Safe fallback if DNS lookup fails temporarily
            if (empty($domain)) {
                $_SESSION['flash_error'] = 'Invalid domain structure.';
                Response::redirect('/dashboard');
            }
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

        // Method 2: Safe HTTP File verification fallback (/.well-known/shieldlayer-verification.txt)
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
}
