<?php

namespace ShieldLayer\Repositories;

use ShieldLayer\Core\Database;
use PDO;

class TenantRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $id, string $name, string $slug, string $plan = 'starter'): bool
    {
        $stmt = $this->db->prepare("INSERT INTO tenants (id, name, slug, plan) VALUES (:id, :name, :slug, :plan)");
        return $stmt->execute([
            ':id' => $id,
            ':name' => trim($name),
            ':slug' => strtolower(trim($slug)),
            ':plan' => $plan,
        ]);
    }

    public function attachUser(string $tenantId, string $userId, string $role = 'owner'): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tenant_users (tenant_id, user_id, role) 
            VALUES (:tenant_id, :user_id, :role)
            ON DUPLICATE KEY UPDATE role = :role_update
        ");
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $userId,
            ':role' => $role,
            ':role_update' => $role
        ]);
    }

    public function getTenantsForUser(string $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.name, t.slug, t.plan, tu.role, t.created_at
            FROM tenants t
            INNER JOIN tenant_users tu ON t.id = tu.tenant_id
            WHERE tu.user_id = :user_id
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $tenant = $stmt->fetch();
        return $tenant ?: null;
    }

    public function getMembers(string $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, tu.role, tu.created_at
            FROM users u
            INNER JOIN tenant_users tu ON u.id = tu.user_id
            WHERE tu.tenant_id = :tenant_id
            ORDER BY tu.created_at ASC
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function removeUser(string $tenantId, string $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tenant_users WHERE tenant_id = :tenant_id AND user_id = :user_id");
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $userId
        ]);
    }
}
