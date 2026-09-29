<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\TenantContext;
use ShieldLayer\Repositories\TenantRepository;
use ShieldLayer\Support\Security;
use ShieldLayer\Core\Response;

class TenantMiddleware
{
    public static function handle(): void
    {
        Security::startSession();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            Response::redirect('/login');
        }

        $activeTenantId = $_SESSION['active_tenant_id'] ?? null;
        $tenantRepo = new TenantRepository();

        if ($activeTenantId) {
            $tenant = $tenantRepo->findById($activeTenantId);
            if ($tenant) {
                TenantContext::setTenant($tenant);
                return;
            }
        }

        // Auto-select first available tenant for user
        $userTenants = $tenantRepo->getTenantsForUser($userId);
        if (!empty($userTenants)) {
            $_SESSION['active_tenant_id'] = $userTenants[0]['id'];
            TenantContext::setTenant($userTenants[0]);
        }
    }
}
