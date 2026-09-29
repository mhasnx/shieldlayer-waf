<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Services\TenantService;
use ShieldLayer\Middleware\CsrfMiddleware;
use ShieldLayer\Middleware\AuthMiddleware;
use ShieldLayer\Support\Security;

class TenantController
{
    private TenantService $tenantService;

    public function __construct()
    {
        $this->tenantService = new TenantService();
    }

    public function store(Request $request): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle($request);

        Security::startSession();
        $userId = $_SESSION['user_id'];
        $name = $request->input('org_name', '');

        $result = $this->tenantService->createTenant($name, $userId);

        if ($result['success']) {
            $_SESSION['active_tenant_id'] = $result['tenant_id'];
            $_SESSION['flash_success'] = "Organization '{$result['name']}' created and set active.";
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        Response::redirect('/shieldlayer/public/dashboard');
    }

    public function switchTenant(Request $request): void
    {
        AuthMiddleware::handle();
        Security::startSession();

        $userId = $_SESSION['user_id'];
        $targetTenantId = $request->input('tenant_id');

        $userTenants = $this->tenantService->getUserTenants($userId);
        $found = false;

        foreach ($userTenants as $t) {
            if ($t['id'] === $targetTenantId) {
                $_SESSION['active_tenant_id'] = $t['id'];
                $_SESSION['flash_success'] = "Switched scope to {$t['name']}.";
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['flash_error'] = 'Unauthorized tenant scope switch.';
        }

        Response::redirect('/shieldlayer/public/dashboard');
    }
}
