<?php

namespace App\Observers;

use App\Jobs\ProvisionTenantJob;
use App\Models\Tenant;
use App\Models\Detail;
use App\Services\HostedTenantService;
use Illuminate\Support\Str;

class TenantObserver
{

    public function created(Tenant $tenant): void
    {
        $databaseName = Str::replace(".", "_", request()->domain_name);
        $uin = (string) Str::uuid();

        $tenantDetail = Detail::create([
            'db' => $databaseName,
            'domain' => request()->domain_name,
            'uin' => $uin,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', 'Sterlite@2025'),
        ]);

        if ($tenantDetail) {
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                try {
                    $hostedService = app(HostedTenantService::class);
                    $hostedService->syncUinToExternalSystem(request()->domain_name, $uin);
                } catch (\Exception $e) {
                }
            }
            $requestData = request()->only([
                'first_name',
                'last_name',
                'password',
                'email',
                'username',
                'domain_name',
                'mobile'
            ]);

            ProvisionTenantJob::dispatch($tenant, $databaseName, $requestData);
        }
    }
}
