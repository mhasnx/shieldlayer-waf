<?php

namespace ShieldLayer\Core;

class TenantContext
{
    private static ?array $currentTenant = null;

    public static function setTenant(array $tenant): void
    {
        self::$currentTenant = $tenant;
    }

    public static function getTenant(): ?array
    {
        return self::$currentTenant;
    }

    public static function getTenantId(): ?string
    {
        return self::$currentTenant['id'] ?? null;
    }

    public static function clear(): void
    {
        self::$currentTenant = null;
    }
}
