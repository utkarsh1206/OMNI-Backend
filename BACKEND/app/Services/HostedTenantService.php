<?php
// NOC-995 To-Do Hosted MultiTenant
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class HostedTenantService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('omni.HOSTED_URL') . '/tenant/api';
    }

    /**
     * Get the Auth Token from the Parent API
     */
    public function getToken(): string
    {
        $response = Http::withoutVerifying()->post("{$this->baseUrl}/login.php", [
            'TENANT_AUTH_USER' => config('omni.TENANT_AUTH_USER'),
            'TENANT_AUTH_PASS' => config('omni.TENANT_AUTH_PASS'),
        ]);

        $data = $response->json() ?? [];

        if ($response->successful() && isset($data['code']) && $data['code'] === 0) {
            return $data['token'];
        }

        throw new Exception("Authentication Failed: Code: " . ($data['code'] ?? 'Unknown Code') . " Message: " . ($data['message'] ?? 'Unknown Error'));
    }

    /**
     * Create Tenant on External System
     */
    public function createExternalTenant(array $payload)
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withHeaders(['Authorization' => $token])
            ->post("{$this->baseUrl}/tenantCreate.php", $payload);

        $data = $response->json() ?? [];

        // Check for specific success codes: 0 (General Success) or 1000021 (Already Exists/Specific Success)
        if (in_array($data['code'], [0, 1000021])) {
            return $data;
        }

        throw new Exception("External API Creation Failed: Code: " . ($data['code'] ?? 'Unknown Code') . " Message: " . ($data['message'] ?? 'Unknown Error'));
    }

    /**
     * Update Tenant on External System
     * PUT .../tenantDetailsUpdate.php?domain=example.com
     */
    public function updateExternalTenant(string $domainName, array $payload)
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withHeaders(['Authorization' => $token])
            ->put("{$this->baseUrl}/tenantDetailsUpdate.php?domain={$domainName}", $payload);

        $data = $response->json() ?? [];

        if ($response->successful() && isset($data['code']) && $data['code'] === 0) {
            return $data;
        }

        throw new Exception("External API Update Failed: Code: " . ($data['code'] ?? 'Unknown Code') . " Message: " . ($data['message'] ?? 'Unknown Error'));
    }

    /**
     * Delete Tenant from External System
     * URL format: .../tenantDelete.php?domain=example.com
     */
    public function deleteExternalTenant(string $domainName)
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withHeaders(['Authorization' => $token])
            ->delete("{$this->baseUrl}/tenantDelete.php?domain={$domainName}");

        $data = $response->json() ?? [];

        // Assuming code 0 is success as per previous logic
        if ($response->successful() && isset($data['code']) && $data['code'] === 0) {
            return $data;
        }

        throw new Exception("External API Deletion Failed: Code: " . ($data['code'] ?? 'Unknown Code') . " Message: " . ($data['message'] ?? 'Unknown Error'));
    }


    /**
     * Notify External System of the generated UIN for a domain
     * POST .../omniApiTokenForDCAgent.php?domain=example.com
     */
    public function syncUinToExternalSystem(string $domainName, string $uin)
    {
        try {
            $token = $this->getToken();
            $domainLower = strToLower($domainName);

            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => $token])
                ->post("{$this->baseUrl}/omniApiTokenForDCAgent.php?domain={$domainLower}", [
                    'UIN' => $uin
                ]);

            $data = $response->json() ?? [];

            if (!$response->successful() || (isset($data['code']) && $data['code'] !== 0)) {
                generateLogs('error', 'UIN Sync Failed for domain: ' . $domainName, json_encode($data));
            }

            return $data;
        } catch (Exception $e) {
            generateLogs('error', 'Exception during UIN Sync for domain: ' . $domainName, $e->getMessage());
            return null;
        }
    }
}
