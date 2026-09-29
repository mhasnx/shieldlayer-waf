<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\Response;
use ShieldLayer\Support\Security;

class AuthMiddleware
{
    public static function handle(): void
    {
        Security::startSession();
        if (empty($_SESSION['user_id'])) {
            Response::redirect('/shieldlayer/public/login');
        }
    }
}
