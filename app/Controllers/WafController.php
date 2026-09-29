<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Core\TenantContext;
use ShieldLayer\Repositories\WafRuleRepository;
use ShieldLayer\Middleware\AuthMiddleware;
use ShieldLayer\Middleware\CsrfMiddleware;
use ShieldLayer\Middleware\RoleMiddleware;
use ShieldLayer\Support\Security;

class WafController
{
    private WafRuleRepository $ruleRepo;

    public function __construct()
    {
        $this->ruleRepo = new WafRuleRepository();
    }

    public function store(Request $request): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle($request);
        RoleMiddleware::authorize(['owner', 'analyst']);

        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            Response::redirect('/shieldlayer/public/dashboard');
        }

        $ruleType = $request->input('rule_type', 'ip_block');
        $pattern = $request->input('pattern', '');
        $action = $request->input('action', 'block');

        if (!empty($pattern)) {
            $this->ruleRepo->create(Security::generateUuid(), $tenantId, $ruleType, $pattern, $action);
            $_SESSION['flash_success'] = 'WAF Rule successfully provisioned.';
        } else {
            $_SESSION['flash_error'] = 'Rule pattern/value cannot be empty.';
        }

        Response::redirect('/shieldlayer/public/dashboard');
    }

    public function delete(Request $request): void
    {
        AuthMiddleware::handle();
        RoleMiddleware::authorize(['owner', 'analyst']);

        $tenantId = TenantContext::getTenantId();
        $ruleId = $request->input('id');

        if ($tenantId && $ruleId) {
            $this->ruleRepo->delete($ruleId, $tenantId);
            $_SESSION['flash_success'] = 'WAF Rule deleted.';
        }

        Response::redirect('/shieldlayer/public/dashboard');
    }
}