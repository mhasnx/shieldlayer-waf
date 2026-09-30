<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Handle Render HTTPS reverse proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

use ShieldLayer\Support\Env;
use ShieldLayer\Support\Security;
use ShieldLayer\Core\Request;
use ShieldLayer\Core\Router;
use ShieldLayer\Middleware\TenantMiddleware;
use ShieldLayer\Middleware\WafMiddleware;
use ShieldLayer\Middleware\RateLimitMiddleware;
use ShieldLayer\Controllers\AuthController;
use ShieldLayer\Controllers\DashboardController;
use ShieldLayer\Controllers\TenantController;
use ShieldLayer\Controllers\WafController;
use ShieldLayer\Controllers\TeamController;

Env::load(__DIR__ . '/../.env');
\ShieldLayer\Core\Migrator::run();
Security::startSession();

$request = new Request();

// Resolve active tenant from session first (so WAF and RateLimiter know the tenant scope)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$publicRoutes = ['/', '/login', '/register', '/logout'];

if (!in_array($path, $publicRoutes)) {
    TenantMiddleware::handle();
}

// Rate Limit & WAF protection with active tenant context
RateLimitMiddleware::handle($request);
WafMiddleware::handle($request);

$router = new Router();

// Auth Routes (Public)
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'handleRegister']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard & Metrics
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/export', [DashboardController::class, 'exportTelemetry']);

// Tenant Management
$router->get('/tenant/switch', [TenantController::class, 'switchTenant']);
$router->post('/tenant/create', [TenantController::class, 'store']);

// WAF Rules Routes
$router->post('/waf/rule/create', [WafController::class, 'store']);
$router->get('/waf/rule/delete', [WafController::class, 'delete']);

// Team & RBAC Routes
$router->post('/team/invite', [TeamController::class, 'invite']);
$router->get('/team/remove', [TeamController::class, 'remove']);

$router->dispatch($request);