<?php

namespace ShieldLayer\Services;

use ShieldLayer\Repositories\TenantRepository;
use ShieldLayer\Support\Security;
use ShieldLayer\Support\Logger;

class TenantService
{
    private TenantRepository $tenantRepository;

    public function __construct()
    {
        $this->tenantRepository = new TenantRepository();
    }

    public function createTenant(string $name, string $userId): array
    {
        $cleanName = trim($name);
        if (strlen($cleanName) < 3) {
            return ['success' => false, 'message' => 'Organization name must be at least 3 characters.'];
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $cleanName));
        $slug = trim($slug, '-') . '-' . substr(bin2hex(random_bytes(2)), 0, 4);

        $tenantId = Security::generateUuid();

        $created = $this->tenantRepository->create($tenantId, $cleanName, $slug, 'starter');
        if (!$created) {
            return ['success' => false, 'message' => 'Failed to provision organization.'];
        }

        $this->tenantRepository->attachUser($tenantId, $userId, 'owner');
        Logger::audit('TENANT_CREATED', $userId, ['tenant_id' => $tenantId, 'name' => $cleanName]);

        return ['success' => true, 'tenant_id' => $tenantId, 'name' => $cleanName];
    }

    public function getUserTenants(string $userId): array
    {
        return $this->tenantRepository->getTenantsForUser($userId);
    }
}
