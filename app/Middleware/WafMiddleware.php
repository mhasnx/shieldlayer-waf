<?php

namespace ShieldLayer\Middleware;

use ShieldLayer\Core\Request;
use ShieldLayer\Services\WafEngine;

class WafMiddleware
{
    public static function handle(Request $request): void
    {
        $waf = new WafEngine();
        $waf->inspect($request);
    }
}
