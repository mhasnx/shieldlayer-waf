<?php

namespace ShieldLayer\Services;

class SystemHealthService
{
    public static function assess(): array
    {
        $checks = [];
        $score = 100;

        // 1. PHP Version
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.1.0', '>=');
        $checks[] = [
            'name' => 'PHP Runtime Version',
            'status' => $phpOk ? 'PASS' : 'WARN',
            'details' => "Running PHP {$phpVersion}"
        ];
        if (!$phpOk) $score -= 15;

        // 2. Display Errors Directive
        $displayErrors = ini_get('display_errors');
        $errorsHidden = empty($displayErrors) || $displayErrors === '0' || strtolower($displayErrors) === 'off';
        $checks[] = [
            'name' => 'Error Leakage Prevention',
            'status' => $errorsHidden ? 'PASS' : 'FAIL',
            'details' => $errorsHidden ? 'display_errors is disabled' : 'display_errors is enabled (info leak risk)'
        ];
        if (!$errorsHidden) $score -= 25;

        // 3. Session Cookie Security
        $cookieParams = session_get_cookie_params();
        $cookieSecure = !empty($cookieParams['httponly']) && $cookieParams['samesite'] === 'Strict';
        $checks[] = [
            'name' => 'Session Cookie Hardening',
            'status' => $cookieSecure ? 'PASS' : 'WARN',
            'details' => $cookieSecure ? 'HttpOnly & SameSite=Strict active' : 'Cookies lack strict scoping'
        ];
        if (!$cookieSecure) $score -= 15;

        // 4. Expose PHP Check
        $exposePhp = ini_get('expose_php');
        $hidePhp = empty($exposePhp) || $exposePhp === '0' || strtolower($exposePhp) === 'off';
        $checks[] = [
            'name' => 'Server Fingerprint Concealment',
            'status' => $hidePhp ? 'PASS' : 'WARN',
            'details' => $hidePhp ? 'expose_php disabled' : 'expose_php active'
        ];
        if (!$hidePhp) $score -= 10;

        // Calculate Grade
        $grade = 'A+';
        if ($score < 60) $grade = 'C';
        elseif ($score < 80) $grade = 'B';
        elseif ($score < 95) $grade = 'A';

        return [
            'score' => max(0, $score),
            'grade' => $grade,
            'checks' => $checks
        ];
    }
}
