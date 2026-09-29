<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
Security::startSession();

$router = new Router();

// Auth Routes
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'handleRegister']);
$router->get('/logout', [AuthController::class, 'logout']);

// Tenant & Dashboard Routes
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/export', [DashboardController::class, 'exportTelemetry']);
$router->post('/tenant/create', [TenantController::class, 'store']);
$router->get('/tenant/switch', [TenantController::class, 'switchTenant']);

// WAF Rules Routes
$router->post('/waf/rule/create', [WafController::class, 'store']);
$router->get('/waf/rule/delete', [WafController::class, 'delete']);

// Team & RBAC Routes
$router->post('/team/invite', [TeamController::class, 'invite']);
$router->get('/team/remove', [TeamController::class, 'remove']);

// Lifecycle Execution
$request = new Request();

TenantMiddleware::handle();
RateLimitMiddleware::handle($request);
WafMiddleware::handle($request);

$router->dispatch($request);