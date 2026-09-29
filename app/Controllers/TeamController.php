<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Core\TenantContext;
use ShieldLayer\Repositories\TenantRepository;
use ShieldLayer\Repositories\UserRepository;
use ShieldLayer\Middleware\AuthMiddleware;
use ShieldLayer\Middleware\CsrfMiddleware;
use ShieldLayer\Middleware\RoleMiddleware;
use ShieldLayer\Support\Security;

class TeamController
{
    private TenantRepository $tenantRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->tenantRepo = new TenantRepository();
        $this->userRepo = new UserRepository();
    }

    public function invite(Request $request): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle($request);
        RoleMiddleware::authorize(['owner']);

        $tenantId = TenantContext::getTenantId();
        $email = trim($request->input('email', ''));
        $role = $request->input('role', 'analyst');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Invalid email address specified.';
            Response::redirect('/dashboard');
        }

        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            $_SESSION['flash_error'] = "Operator with email {$email} does not exist in ShieldLayer.";
            Response::redirect('/dashboard');
        }

        $this->tenantRepo->attachUser($tenantId, $user['id'], $role);
        $_SESSION['flash_success'] = "Operator {$email} added to organization as {$role}.";

        Response::redirect('/dashboard');
    }

    public function remove(Request $request): void
    {
        AuthMiddleware::handle();
        RoleMiddleware::authorize(['owner']);

        $tenantId = TenantContext::getTenantId();
        $userId = $request->input('user_id');

        Security::startSession();
        if ($userId === $_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Cannot revoke your own active ownership seat.';
            Response::redirect('/dashboard');
        }

        if ($tenantId && $userId) {
            $this->tenantRepo->removeUser($tenantId, $userId);
            $_SESSION['flash_success'] = 'Member access revoked from tenant scope.';
        }

        Response::redirect('/dashboard');
    }
}
