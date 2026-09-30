<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Support\Security;

class CsrfMiddleware
{
    public static function handle(Request $request): void
    {
        Security::startSession();

        if (in_array(strtoupper($request->getMethod()), ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }

        $token = $request->input('csrf_token') 
            ?? $_POST['csrf_token'] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? null;

        $sessionToken = $_SESSION['csrf_token'] ?? null;

        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            Response::json([
                'error' => true,
                'message' => 'CSRF Token Validation Failed. Request Terminated.'
            ], 403);
            exit;
        }
    }
}