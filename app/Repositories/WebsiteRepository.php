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
        $this->ensureTables();
    }

    private function ensureTables(): void
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS websites (
                    id VARCHAR(36) PRIMARY KEY,
                    tenant_id VARCHAR(36) NOT NULL,
                    domain VARCHAR(255) NOT NULL,
                    origin_url VARCHAR(500) NOT NULL,
                    status VARCHAR(32) DEFAULT 'OWNERSHIP_REQUIRED',
                    verification_token VARCHAR(64) NOT NULL,
                    verified_at DATETIME NULL,
                    traffic_verified_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS website_verifications (
                    id VARCHAR(36) PRIMARY KEY,
                    website_id VARCHAR(36) NOT NULL,
                    method VARCHAR(32) DEFAULT 'DNS_TXT',
                    token VARCHAR(64) NOT NULL,
                    status VARCHAR(32) DEFAULT 'PENDING',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS security_scans (
                    id VARCHAR(36) PRIMARY KEY,
                    website_id VARCHAR(36) NOT NULL,
                    score INT DEFAULT 100,
                    status VARCHAR(32) DEFAULT 'PENDING',
                    summary TEXT NULL,
                    completed_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS scan_findings (
                    id VARCHAR(36) PRIMARY KEY,
                    scan_id VARCHAR(36) NOT NULL,
                    website_id VARCHAR(36) NOT NULL,
                    severity VARCHAR(32) DEFAULT 'LOW',
                    category VARCHAR(64) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    impact TEXT NOT NULL,
                    recommendation TEXT NOT NULL,
                    evidence TEXT NULL,
                    status VARCHAR(32) DEFAULT 'OPEN',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } catch (\Throwable $e) {
            error_log("Schema ensure notice: " . $e->getMessage());
        }
    }

    public function create(string $tenantId, string $domain, string $originUrl): array
    {
        $this->ensureTables();

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
        $this->ensureTables();
        $stmt = $this->db->prepare("SELECT * FROM websites WHERE tenant_id = :tenant_id ORDER BY created_at DESC");
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(string $id): ?array
    {
        $this->ensureTables();
        $stmt = $this->db->prepare("SELECT * FROM websites WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markVerified(string $id): bool
    {
        $this->ensureTables();
        $stmt = $this->db->prepare("
            UPDATE websites 
            SET status = 'PROTECTION_READY', verified_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $id]);
    }
}