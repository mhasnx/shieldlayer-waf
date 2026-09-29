<?php

namespace ShieldLayer\Services;

class TenantWebhookService
{
    public static function dispatch(string $webhookUrl, array $eventData): void
    {
        if (empty($webhookUrl) || !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return;
        }

        $payload = json_encode([
            'platform' => 'ShieldLayer WAF Telemetry',
            'timestamp' => date('Y-m-d H:i:s'),
            'alert_level' => 'HIGH_PRIORITY_INCIDENT',
            'incident' => $eventData
        ]);

        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2, // Non-blocking fast timeout
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
