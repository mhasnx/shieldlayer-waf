<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Support\Security;

class CsrfMiddleware
{
    public static function handle(Request $request): void
    {
        if ($request->getMethod() === 'POST') {
            $token = $request->input('_csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Security::validateCsrfToken($token)) {
                Response::json([
                    'error' => true,
                    'message' => 'CSRF Token Validation Failed. Request Terminated.'
                ], 403);
            }
        }
    }
}
