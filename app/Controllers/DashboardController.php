<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\View;
use ShieldLayer\Core\Response;
use ShieldLayer\Middleware\AuthMiddleware;
use ShieldLayer\Middleware\TenantMiddleware;
use ShieldLayer\Core\TenantContext;
use ShieldLayer\Services\TenantService;
use ShieldLayer\Services\SystemHealthService;
use ShieldLayer\Repositories\TenantRepository;
use ShieldLayer\Repositories\SecurityEventRepository;
use ShieldLayer\Repositories\WafRuleRepository;
use ShieldLayer\Support\Security;

class DashboardController
{
    public function index(Request $request): void
    {
        AuthMiddleware::handle();
        TenantMiddleware::handle();
        Security::startSession();

        $userId = $_SESSION['user_id'];
        $tenantService = new TenantService();
        $tenantRepo = new TenantRepository();
        $eventRepo = new SecurityEventRepository();
        $ruleRepo = new WafRuleRepository();

        $userTenants = $tenantService->getUserTenants($userId);
        $currentTenant = TenantContext::getTenant();

        $securityEvents = [];
        $wafRules = [];
        $threatMetrics = [];
        $teamMembers = [];
        $tenantRole = 'viewer';
        $totalThreats = 0;

        if ($currentTenant) {
            $securityEvents = $eventRepo->getRecentEventsByTenant($currentTenant['id'], 15);
            $totalThreats = $eventRepo->getThreatCountByTenant($currentTenant['id']);
            $wafRules = $ruleRepo->getActiveRulesByTenant($currentTenant['id']);
            $threatMetrics = $eventRepo->getThreatMetrics($currentTenant['id']);
            $teamMembers = $tenantRepo->getMembers($currentTenant['id']);

            foreach ($userTenants as $t) {
                if ($t['id'] === $currentTenant['id']) {
                    $tenantRole = $t['role'];
                    break;
                }
            }
        }

        $healthStatus = SystemHealthService::assess();

        View::render('dashboard/index', [
            'title' => 'ShieldLayer — Security Operations Center',
            'user_name' => $_SESSION['user_name'] ?? 'Analyst',
            'user_email' => $_SESSION['user_email'] ?? '',
            'user_role' => $_SESSION['user_role'] ?? 'user',
            'tenant_role' => $tenantRole,
            'csrf_token' => Security::csrfToken(),
            'current_tenant' => $currentTenant,
            'user_tenants' => $userTenants,
            'team_members' => $teamMembers,
            'security_events' => $securityEvents,
            'waf_rules' => $wafRules,
            'threat_metrics' => $threatMetrics,
            'total_threats' => $totalThreats,
            'health_status' => $healthStatus,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function exportTelemetry(Request $request): void
    {
        AuthMiddleware::handle();
        TenantMiddleware::handle();

        $currentTenant = TenantContext::getTenant();
        if (!$currentTenant) {
            Response::redirect('/shieldlayer/public/dashboard');
        }

        $eventRepo = new SecurityEventRepository();
        $events = $eventRepo->getAllEventsForExport($currentTenant['id']);

        $filename = 'threat_telemetry_' . ($currentTenant['slug'] ?? 'export') . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}");

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Event ID', 'Threat Vector', 'Severity', 'Source IP', 'Request URI', 'Payload Sample', 'Action Taken', 'Timestamp']);

        foreach ($events as $row) {
            fputcsv($output, [
                $row['id'],
                $row['threat_type'],
                $row['severity'],
                $row['source_ip'],
                $row['request_uri'],
                $row['payload_sample'],
                $row['action_taken'],
                $row['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}