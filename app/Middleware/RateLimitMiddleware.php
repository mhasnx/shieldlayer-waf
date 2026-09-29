<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\Request;
use ShieldLayer\Services\RateLimiter;

class RateLimitMiddleware
{
    public static function handle(Request $request): void
    {
        $limiter = new RateLimiter();
        $limiter->check($request);
    }
}
