<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\TenantLicense;
use Carbon\Carbon;
use App\Http\Response\CustomApiResponse;

class SuperAdminDashboardRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index()
    {
        $totalTenant = $this->getTotalTenant();
        $licensesExpiring = $this->getLicensesExpiringAfter10Days();

        $data = [
            'total_tenant' => $totalTenant,
            'licenses_expiring_after_10_days' => $licensesExpiring,
        ];

        return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_fetch'));
    }

    public function getTotalTenant()
    {
        return Tenant::whereNull('deleted_at')->count();
    }

    public function getLicensesExpiringAfter10Days()
    {
        $today = Carbon::now()->timestamp;
        $tenDaysLater = Carbon::now()->addDays(10)->timestamp;

        $licenses = TenantLicense::whereBetween('expire_date', [$today, $tenDaysLater])
            ->with('tenant')
            ->get(['tenant_id', 'expire_date']);

        return $licenses->map(function ($license) {
            return [
                'tenant_id'   => $license->tenant_id,
                'tenant_name' => $license->tenant_name,
                'expire_date' => Carbon::createFromTimestamp($license->expire_date)->format('d/m/Y'),
            ];
        });
    }
}