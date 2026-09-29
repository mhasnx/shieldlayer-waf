<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\TenantContext;
use ShieldLayer\Core\Response;
use ShieldLayer\Repositories\TenantRepository;
use ShieldLayer\Support\Security;

class RoleMiddleware
{
    public static function authorize(array $allowedRoles): void
    {
        Security::startSession();
        $userId = $_SESSION['user_id'] ?? null;
        $tenantId = TenantContext::getTenantId();

        if (!$userId || !$tenantId) {
            Response::redirect('/login');
        }

        $tenantRepo = new TenantRepository();
        $userTenants = $tenantRepo->getTenantsForUser($userId);

        $currentRole = null;
        foreach ($userTenants as $t) {
            if ($t['id'] === $tenantId) {
                $currentRole = $t['role'];
                break;
            }
        }

        if (!$currentRole || !in_array($currentRole, $allowedRoles, true)) {
            Response::json([
                'error' => true,
                'code' => 403,
                'message' => 'ShieldLayer RBAC: Unauthorized operation. Insufficient tenant privileges.'
            ], 403);
        }
    }
}
