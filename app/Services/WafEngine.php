<?php

namespace ShieldLayer\Services;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Core\TenantContext;
use ShieldLayer\Repositories\SecurityEventRepository;
use ShieldLayer\Repositories\WafRuleRepository;

class WafEngine
{
    private SecurityEventRepository $eventRepo;
    private WafRuleRepository $ruleRepo;

    private array $sqliPatterns = [
        '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE)\b.*\b(FROM|INTO|TABLE|DATABASE|WHERE)\b)/i',
        '/(\bOR\b\s+\d+=\d+|\bAND\b\s+\d+=\d+)/i',
        '/(\'\s*OR\s*\'1\'\s*=\s*\'1)/i',
        '/(INFORMATION_SCHEMA|BENCHMARK\(|SLEEP\()/i'
    ];

    private array $xssPatterns = [
        '/<script\b[^>]*>(.*?)<\/script>/is',
        '/\bon(load|error|click|mouseover|focus)\s*=/i',
        '/javascript:/i'
    ];

    private array $pathTraversalPatterns = [
        '/(\.\.\/|\.\.\\\)/i',
        '/(\/etc\/passwd|\/windows\/system32)/i'
    ];

    public function __construct()
    {
        $this->eventRepo = new SecurityEventRepository();
        $this->ruleRepo = new WafRuleRepository();
    }

    public function inspect(Request $request): void
    {
        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            return;
        }

        // 1. Evaluate Dynamic Custom Tenant Rules (e.g. IP Blocking)
        $rules = $this->ruleRepo->getActiveRulesByTenant($tenantId);
        $clientIp = $request->getIp();

        foreach ($rules as $rule) {
            if ($rule['rule_type'] === 'ip_block' && $clientIp === $rule['pattern']) {
                $this->blockAndLog($tenantId, 'IP_BLACKLISTED', 'high', $request, "Blocked IP: {$clientIp}");
            }
        }

        // 2. Evaluate Core Payload Threat Signatures
        $allData = $request->all();
        $this->scanRecursive($allData, $request, $tenantId);
    }

    private function scanRecursive(mixed $data, Request $request, string $tenantId): void
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                $this->scanRecursive($value, $request, $tenantId);
            }
            return;
        }

        $inputStr = (string) $data;
        if (empty($inputStr)) {
            return;
        }

        foreach ($this->sqliPatterns as $pattern) {
            if (preg_match($pattern, $inputStr)) {
                $this->blockAndLog($tenantId, 'SQL_INJECTION', 'critical', $request, $inputStr);
            }
        }

        foreach ($this->xssPatterns as $pattern) {
            if (preg_match($pattern, $inputStr)) {
                $this->blockAndLog($tenantId, 'CROSS_SITE_SCRIPTING', 'high', $request, $inputStr);
            }
        }

        foreach ($this->pathTraversalPatterns as $pattern) {
            if (preg_match($pattern, $inputStr)) {
                $this->blockAndLog($tenantId, 'PATH_TRAVERSAL', 'high', $request, $inputStr);
            }
        }
    }

    private function blockAndLog(string $tenantId, string $threatType, string $severity, Request $request, string $sample): void
    {
        $this->eventRepo->logEvent(
            $tenantId,
            $threatType,
            $severity,
            $request->getIp(),
            $request->getUri(),
            $sample,
            'BLOCKED'
        );

        Response::json([
            'error' => true,
            'code' => 403,
            'message' => 'ShieldLayer WAF: Malicious payload signature detected and blocked.',
            'threat' => $threatType,
            'ip' => $request->getIp()
        ], 403);
    }
}
