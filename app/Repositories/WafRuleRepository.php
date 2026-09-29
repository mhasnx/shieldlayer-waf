<?php

namespace ShieldLayer\Repositories;

use ShieldLayer\Core\Database;
use PDO;

class WafRuleRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $id, string $tenantId, string $ruleType, string $pattern, string $action = 'block'): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO waf_rules (id, tenant_id, rule_type, pattern, action, is_active)
            VALUES (:id, :tenant_id, :rule_type, :pattern, :action, 1)
        ");
        return $stmt->execute([
            ':id' => $id,
            ':tenant_id' => $tenantId,
            ':rule_type' => $ruleType,
            ':pattern' => trim($pattern),
            ':action' => $action
        ]);
    }

    public function getActiveRulesByTenant(string $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM waf_rules 
            WHERE tenant_id = :tenant_id AND is_active = 1
            ORDER BY created_at DESC
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function delete(string $id, string $tenantId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM waf_rules WHERE id = :id AND tenant_id = :tenant_id");
        return $stmt->execute([
            ':id' => $id,
            ':tenant_id' => $tenantId
        ]);
    }
}
