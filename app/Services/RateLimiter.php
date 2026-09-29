<?php

namespace ShieldLayer\Services;

use ShieldLayer\Core\Database;
use ShieldLayer\Core\Request;
use ShieldLayer\Core\Response;
use ShieldLayer\Core\TenantContext;
use ShieldLayer\Repositories\SecurityEventRepository;
use PDO;

class RateLimiter
{
    private PDO $db;
    private SecurityEventRepository $eventRepo;
    private int $maxRequests = 60; // Max allowed requests
    private int $decaySeconds = 60; // Window size in seconds (1 minute)

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->eventRepo = new SecurityEventRepository();
    }

    public function check(Request $request): void
    {
        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            return;
        }

        $ip = $request->getIp();
        $identifier = hash('sha256', $ip . '_' . $request->getUri());
        $now = time();

        $stmt = $this->db->prepare("
            SELECT id, hits, window_start 
            FROM rate_limits 
            WHERE tenant_id = :tenant_id AND identifier = :identifier 
            LIMIT 1
        ");
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':identifier' => $identifier
        ]);
        $record = $stmt->fetch();

        if (!$record) {
            $insert = $this->db->prepare("
                INSERT INTO rate_limits (tenant_id, identifier, hits, window_start) 
                VALUES (:tenant_id, :identifier, 1, :now)
            ");
            $insert->execute([
                ':tenant_id' => $tenantId,
                ':identifier' => $identifier,
                ':now' => $now
            ]);
            return;
        }

        // Check if window expired
        if ($now - (int)$record['window_start'] > $this->decaySeconds) {
            $reset = $this->db->prepare("
                UPDATE rate_limits 
                SET hits = 1, window_start = :now 
                WHERE id = :id
            ");
            $reset->execute([':now' => $now, ':id' => $record['id']]);
            return;
        }

        // If limit exceeded
        if ((int)$record['hits'] >= $this->maxRequests) {
            $this->eventRepo->logEvent(
                $tenantId,
                'RATE_LIMIT_EXCEEDED',
                'medium',
                $ip,
                $request->getUri(),
                "Burst threshold exceeded: {$record['hits']} reqs/min",
                'THROTTLED'
            );

            Response::json([
                'error' => true,
                'code' => 429,
                'message' => 'ShieldLayer WAF: Rate limit exceeded. Request throttled.',
                'retry_after_seconds' => $this->decaySeconds - ($now - (int)$record['window_start'])
            ], 429);
        }

        // Increment hit count
        $increment = $this->db->prepare("
            UPDATE rate_limits 
            SET hits = hits + 1 
            WHERE id = :id
        ");
        $increment->execute([':id' => $record['id']]);
    }
}