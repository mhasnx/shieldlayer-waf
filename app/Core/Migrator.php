<?php
namespace ShieldLayer\Core;

use Exception;

class Migrator
{
    public static function run(): void
    {
        try {
            $db = Database::getConnection();
            $migrationFile = __DIR__ . "/../../database/migrations/2026_09_30_add_websites_and_scanner_tables.sql";
            if (file_exists($migrationFile)) {
                $sql = file_get_contents($migrationFile);
                $db->exec($sql);
            }
        } catch (Exception $e) {
            error_log("Auto Migration Notice: " . $e->getMessage());
        }
    }
}
