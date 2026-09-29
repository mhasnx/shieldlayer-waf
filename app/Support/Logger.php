<?php

namespace ShieldLayer\Support;

use ShieldLayer\Core\Database;
use PDO;
use Throwable;

class Logger
{
    /**
     * Record an audit action into audit_logs table and file fallback.
     */
    public static function audit(string $action, ?string $userId = null, array $details = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address, user_agent, details) VALUES (:user_id, :action, :ip_address, :user_agent, :details)");
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':ip_address' => $ip,
                ':user_agent' => $userAgent,
                ':details' => !empty($details) ? json_encode($details) : null,
            ]);
        } catch (Throwable $e) {
            // Fallback to local storage log file
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logEntry = sprintf(
                "[%s] ACTION: %s | USER: %s | IP: %s | ERROR: %s\n",
                date('Y-m-d H:i:s'),
                $action,
                $userId ?? 'GUEST',
                $ip,
                $e->getMessage()
            );
            file_put_contents($logDir . '/audit_fallback.log', $logEntry, FILE_APPEND);
        }
    }
}
