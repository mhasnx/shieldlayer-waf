<?php

namespace ShieldLayer\Services;

use ShieldLayer\Core\Database;
use ShieldLayer\Support\Security;
use PDO;

class SecurityScannerService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function scan(string $websiteId, string $domain, string $originUrl): array
    {
        $findings = [];
        $score = 100;

        $targetUrl = preg_match('#^https?://#i', $originUrl) ? $originUrl : "https://{$domain}";

        // 1. Fetch headers safely (timeout 5s, follow redirects)
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'follow_location' => 1,
                'max_redirects' => 5,
                'ignore_errors' => true,
                'user_agent' => 'ShieldLayer-Security-Audit/1.0'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $headers = @get_headers($targetUrl, true, $ctx);
        if (!$headers) {
            return [
                'success' => false,
                'score' => 0,
                'error' => 'Unable to connect to website endpoint.'
            ];
        }

        // Normalize header keys to lowercase
        $normalizedHeaders = [];
        foreach ($headers as $k => $v) {
            if (is_string($k)) {
                $normalizedHeaders[strtolower($k)] = is_array($v) ? end($v) : $v;
            }
        }

        // Check HTTPS
        if (strpos(strtolower($targetUrl), 'https://') !== 0) {
            $score -= 25;
            $findings[] = [
                'severity' => 'HIGH',
                'category' => 'Transport Security',
                'title' => 'HTTPS Not Enforced by Default',
                'impact' => 'Unencrypted plain HTTP communication can allow eavesdropping and tampering.',
                'recommendation' => 'Enable TLS/SSL and configure automatic HTTP to HTTPS redirect.',
                'evidence' => "Target URL: {$targetUrl}"
            ];
        }

        // Check Content-Security-Policy (CSP)
        if (empty($normalizedHeaders['content-security-policy'])) {
            $score -= 20;
            $findings[] = [
                'severity' => 'HIGH',
                'category' => 'Browser Defense',
                'title' => 'Missing Content-Security-Policy (CSP)',
                'impact' => 'Absence of CSP increases vulnerability to Cross-Site Scripting (XSS) and code injection.',
                'recommendation' => 'Deploy a restrictive Content-Security-Policy header restricting script execution.',
                'evidence' => 'No Content-Security-Policy header found in HTTP response.'
            ];
        }

        // Check Strict-Transport-Security (HSTS)
        if (empty($normalizedHeaders['strict-transport-security'])) {
            $score -= 15;
            $findings[] = [
                'severity' => 'MEDIUM',
                'category' => 'Transport Security',
                'title' => 'Missing HTTP Strict Transport Security (HSTS)',
                'impact' => 'Allows protocol downgrade attacks on subsequent user visits.',
                'recommendation' => 'Add Strict-Transport-Security: max-age=31536000; includeSubDomains header.',
                'evidence' => 'No Strict-Transport-Security header present.'
            ];
        }

        // Check X-Frame-Options
        if (empty($normalizedHeaders['x-frame-options']) && empty($normalizedHeaders['content-security-policy'])) {
            $score -= 10;
            $findings[] = [
                'severity' => 'MEDIUM',
                'category' => 'Clickjacking Defense',
                'title' => 'Missing X-Frame-Options Header',
                'impact' => 'The website can be loaded inside an unauthorized iframe, increasing clickjacking risk.',
                'recommendation' => 'Add X-Frame-Options: SAMEORIGIN or DENY to HTTP responses.',
                'evidence' => 'X-Frame-Options header missing.'
            ];
        }

        // Check X-Content-Type-Options
        if (empty($normalizedHeaders['x-content-type-options']) || strtolower($normalizedHeaders['x-content-type-options']) !== 'nosniff') {
            $score -= 10;
            $findings[] = [
                'severity' => 'LOW',
                'category' => 'MIME Sniffing',
                'title' => 'Missing X-Content-Type-Options (nosniff)',
                'impact' => 'Browsers may attempt MIME sniffing, potentially executing non-script files as scripts.',
                'recommendation' => 'Add X-Content-Type-Options: nosniff header.',
                'evidence' => 'X-Content-Type-Options: nosniff not detected.'
            ];
        }

        // Check Server / Runtime Fingerprint Exposure
        $exposedBanners = [];
        if (!empty($normalizedHeaders['server'])) {
            $exposedBanners[] = 'Server: ' . $normalizedHeaders['server'];
        }
        if (!empty($normalizedHeaders['x-powered-by'])) {
            $exposedBanners[] = 'X-Powered-By: ' . $normalizedHeaders['x-powered-by'];
        }

        if (!empty($exposedBanners)) {
            $score -= 10;
            $findings[] = [
                'severity' => 'LOW',
                'category' => 'Information Disclosure',
                'title' => 'Server Fingerprint Disclosed',
                'impact' => 'Exposing web server software and runtime versions assists attackers in reconnaissance.',
                'recommendation' => 'Suppress Server tokens and disable the X-Powered-By response header.',
                'evidence' => implode(', ', $exposedBanners)
            ];
        }

        $finalScore = max(10, min(100, $score));

        // Save Scan to Database
        $scanId = Security::generateUuid();
        $summary = json_encode([
            'total_findings' => count($findings),
            'scanned_at' => date('Y-m-d H:i:s'),
            'endpoint' => $targetUrl
        ]);

        $stmt = $this->db->prepare("
            INSERT INTO security_scans (id, website_id, score, status, summary, completed_at)
            VALUES (:id, :website_id, :score, 'COMPLETED', :summary, NOW())
        ");
        $stmt->execute([
            'id' => $scanId,
            'website_id' => $websiteId,
            'score' => $finalScore,
            'summary' => $summary
        ]);

        // Insert findings
        $stmtFind = $this->db->prepare("
            INSERT INTO scan_findings (id, scan_id, website_id, severity, category, title, impact, recommendation, evidence, status)
            VALUES (:id, :scan_id, :website_id, :severity, :category, :title, :impact, :recommendation, :evidence, 'OPEN')
        ");

        foreach ($findings as $f) {
            $findingId = Security::generateUuid();
            $stmtFind->execute([
                'id' => $findingId,
                'scan_id' => $scanId,
                'website_id' => $websiteId,
                'severity' => $f['severity'],
                'category' => $f['category'],
                'title' => $f['title'],
                'impact' => $f['impact'],
                'recommendation' => $f['recommendation'],
                'evidence' => $f['evidence']
            ]);
        }

        return [
            'success' => true,
            'scan_id' => $scanId,
            'score' => $finalScore,
            'findings_count' => count($findings)
        ];
    }
}