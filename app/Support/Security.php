<?php

namespace ShieldLayer\Support;

class Security
{
    /**
     * Generate a cryptographically secure UUID v4 string.
     */
    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Hash password using Argon2id with automatic secure fallback.
     */
    public static function hashPassword(string $password): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        return password_hash($password, $algo);
    }

    /**
     * Verify password hash against plain text password.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Start hardened session with secure attributes.
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (bool) Env::get('SESSION_SECURE_COOKIE', false);
            
            session_set_cookie_params([
                'lifetime' => 7200,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();

            // Prevent session fixation
            if (!isset($_SESSION['_created_at'])) {
                $_SESSION['_created_at'] = time();
            } elseif (time() - $_SESSION['_created_at'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_created_at'] = time();
            }
        }
    }

    /**
     * Generate or retrieve current CSRF token.
     */
    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Validate submitted CSRF token using timing attack safe comparison.
     */
    public static function validateCsrfToken(?string $token): bool
    {
        self::startSession();
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
}
