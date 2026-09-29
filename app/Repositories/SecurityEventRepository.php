<?php

namespace ShieldLayer\Repositories;

use ShieldLayer\Core\Database;
use PDO;

class SecurityEventRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function logEvent(string $tenantId, string $threatType, string $severity, string $sourceIp, string $uri, ?string $payload, string $action): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO security_events (tenant_id, threat_type, severity, source_ip, request_uri, payload_sample, action_taken)
            VALUES (:tenant_id, :threat_type, :severity, :source_ip, :request_uri, :payload, :action)
        ");
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':threat_type' => $threatType,
            ':severity' => $severity,
            ':source_ip' => $sourceIp,
            ':request_uri' => substr($uri, 0, 255),
            ':payload' => substr($payload ?? '', 0, 1000),
            ':action' => $action
        ]);
    }

    public function getRecentEventsByTenant(string $tenantId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM security_events 
            WHERE tenant_id = :tenant_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':tenant_id', $tenantId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllEventsForExport(string $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, threat_type, severity, source_ip, request_uri, payload_sample, action_taken, created_at 
            FROM security_events 
            WHERE tenant_id = :tenant_id 
            ORDER BY created_at DESC
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function getThreatCountByTenant(string $tenantId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM security_events WHERE tenant_id = :tenant_id");
        $stmt->execute([':tenant_id' => $tenantId]);
        return (int) $stmt->fetchColumn();
    }

    public function getThreatMetrics(string $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT threat_type, COUNT(*) as count 
            FROM security_events 
            WHERE tenant_id = :tenant_id 
            GROUP BY threat_type
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
