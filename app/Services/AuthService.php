<?php

namespace ShieldLayer\Services;

use ShieldLayer\Repositories\UserRepository;
use ShieldLayer\Support\Security;
use ShieldLayer\Support\Logger;
use Exception;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function register(string $name, string $email, string $password): array
    {
        if (strlen($name) < 2) {
            return ['success' => false, 'message' => 'Name must be at least 2 characters long.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please provide a valid email address.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        if ($this->userRepository->findByEmail($email)) {
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }

        $userId = Security::generateUuid();
        $hashedPassword = Security::hashPassword($password);

        $created = $this->userRepository->create([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'user'
        ]);

        if ($created) {
            Logger::audit('AUTH_REGISTER_SUCCESS', $userId, ['email' => $email]);
            return ['success' => true, 'user_id' => $userId];
        }

        return ['success' => false, 'message' => 'Registration failed due to an internal server error.'];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            Logger::audit('AUTH_LOGIN_FAILED', null, ['email' => $email, 'reason' => 'user_not_found']);
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Check if account is temporarily locked
        if (!empty($user['locked_until'])) {
            $lockExpiry = strtotime($user['locked_until']);
            if (time() < $lockExpiry) {
                $waitMinutes = ceil(($lockExpiry - time()) / 60);
                Logger::audit('AUTH_LOGIN_LOCKED_ATTEMPT', $user['id'], ['email' => $email]);
                return ['success' => false, 'message' => "Account is temporarily locked. Try again in {$waitMinutes} minute(s)."];
            }
        }

        // Verify password hash
        if (!Security::verifyPassword($password, $user['password'])) {
            $this->userRepository->incrementFailedLogins($email);
            $failedAttempts = $user['failed_login_attempts'] + 1;

            if ($failedAttempts >= 5) {
                $this->userRepository->lockAccount($email, 15);
                Logger::audit('AUTH_ACCOUNT_LOCKED', $user['id'], ['email' => $email, 'attempts' => $failedAttempts]);
                return ['success' => false, 'message' => 'Too many failed attempts. Account locked for 15 minutes.'];
            }

            Logger::audit('AUTH_LOGIN_FAILED', $user['id'], ['email' => $email, 'attempt' => $failedAttempts]);
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Reset failed logins upon success
        $this->userRepository->resetFailedLogins($email);

        // Regenerate session id to prevent session fixation attacks
        Security::startSession();
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        Logger::audit('AUTH_LOGIN_SUCCESS', $user['id'], ['email' => $email]);

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        Security::startSession();
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            Logger::audit('AUTH_LOGOUT', $userId);
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
