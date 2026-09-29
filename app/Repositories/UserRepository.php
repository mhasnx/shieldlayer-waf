<?php

namespace ShieldLayer\Repositories;

use ShieldLayer\Core\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO users (id, name, email, password, role) VALUES (:id, :name, :email, :password, :role)");
        return $stmt->execute([
            ':id' => $data['id'],
            ':name' => trim($data['name']),
            ':email' => strtolower(trim($data['email'])),
            ':password' => $data['password'],
            ':role' => $data['role'] ?? 'user',
        ]);
    }

    public function incrementFailedLogins(string $email): void
    {
        $stmt = $this->db->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE email = :email");
        $stmt->execute([':email' => strtolower(trim($email))]);
    }

    public function lockAccount(string $email, int $minutes = 15): void
    {
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
        $stmt = $this->db->prepare("UPDATE users SET locked_until = :locked_until WHERE email = :email");
        $stmt->execute([
            ':locked_until' => $lockedUntil,
            ':email' => strtolower(trim($email)),
        ]);
    }

    public function resetFailedLogins(string $email): void
    {
        $stmt = $this->db->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE email = :email");
        $stmt->execute([':email' => strtolower(trim($email))]);
    }
}
