<?php

namespace ShieldLayer\Repositories;

use ShieldLayer\Core\Database;
use ShieldLayer\Support\Security;
use PDO;

class WebsiteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $tenantId, string $domain, string $originUrl): array
    {
        $id = Security::generateUuid();
        $token = 'shieldlayer-verify-' . bin2hex(random_bytes(16));
        $stmt = $this->db->prepare("
            INSERT INTO websites (id, tenant_id, domain, origin_url, status, verification_token, created_at)
            VALUES (:id, :tenant_id, :domain, :origin_url, 'OWNERSHIP_REQUIRED', :token, NOW())
        ");
        $stmt->execute([
            'id' => $id,
            'tenant_id' => $tenantId,
            'domain' => $domain,
            'origin_url' => $originUrl,
            'token' => $token
        ]);

        return [
            'id' => $id,
            'tenant_id' => $tenantId,
            'domain' => $domain,
            'origin_url' => $originUrl,
            'status' => 'OWNERSHIP_REQUIRED',
            'verification_token' => $token
        ];
    }

    public function findByTenant(string $tenantId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM websites WHERE tenant_id = :tenant_id ORDER BY created_at DESC");
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM websites WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markVerified(string $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE websites 
            SET status = 'PROTECTION_READY', verified_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $id]);
    }
}