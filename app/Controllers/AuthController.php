<?php

namespace ShieldLayer\Controllers;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Core\View;
use ShieldLayer\Services\AuthService;
use ShieldLayer\Middleware\CsrfMiddleware;
use ShieldLayer\Support\Security;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(Request $request): void
    {
        Security::startSession();
        if (!empty($_SESSION['user_id'])) {
            Response::redirect('/dashboard');
        }
        View::render('auth/login', [
            'title' => 'ShieldLayer — Sign In',
            'csrf_token' => Security::csrfToken(),
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null
        ]);
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
    }

    public function handleLogin(Request $request): void
    {
        CsrfMiddleware::handle($request);
        $email = $request->input('email', '');
        $password = $request->input('password', '');

        $result = $this->authService->login($email, $password);
        if ($result['success']) {
            Response::redirect('/dashboard');
        }

        Security::startSession();
        $_SESSION['flash_error'] = $result['message'];
        Response::redirect('/login');
    }

    public function showRegister(Request $request): void
    {
        Security::startSession();
        if (!empty($_SESSION['user_id'])) {
            Response::redirect('/dashboard');
        }
        View::render('auth/register', [
            'title' => 'ShieldLayer — Create Account',
            'csrf_token' => Security::csrfToken(),
            'error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_error']);
    }

    public function handleRegister(Request $request): void
    {
        CsrfMiddleware::handle($request);
        $name = $request->input('name', '');
        $email = $request->input('email', '');
        $password = $request->input('password', '');

        $result = $this->authService->register($name, $email, $password);
        Security::startSession();

        if ($result['success']) {
            $_SESSION['flash_success'] = 'Account registered successfully. You may now sign in.';
            Response::redirect('/login');
        }

        $_SESSION['flash_error'] = $result['message'];
        Response::redirect('/register');
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        Response::redirect('/login');
    }
}
