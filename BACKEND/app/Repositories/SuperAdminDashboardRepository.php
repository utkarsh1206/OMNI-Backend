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
        $recentTenants = $this->getRecentlyCreatedTenants();

        $licenseStats = $this->getLicenseAggregates();

        $data = [
            'total_tenant' => $totalTenant,
            'licenses_expiring_after_10_days' => $licensesExpiring,
            'recently_created_tenants' => $recentTenants,
            'total_licenses_issued' => $licenseStats['total_licenses_issued'],
            'total_expired_licenses' => $licenseStats['total_expired_licenses'],
            'total_agents_allocated' => $licenseStats['total_agents_allowed'],
            'total_live_agents_allocated' => $licenseStats['total_live_agents_allowed'],
            'total_managers_allocated' => $licenseStats['total_managers_allowed'],
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

    public function getRecentlyCreatedTenants()
    {
        return Tenant::where('created_at', '>=', Carbon::now()->subDays(7))
            ->whereNull('deleted_at')
            ->get(['id', 'username', 'created_at'])
            ->makeHidden(['tenant_logo_url']);
    }

    public function getLicenseAggregates()
    {
        $now = Carbon::now()->timestamp;

        return TenantLicense::whereHas('tenant', function ($query) {
            $query->whereNull('deleted_at');
        })
            ->selectRaw('
            COUNT(*) as total_licenses_issued,
            SUM(CASE WHEN expire_date < ? THEN 1 ELSE 0 END) as total_expired_licenses,
            SUM(agent_ct) as total_agents_allowed,
            SUM(live_agent_ct) as total_live_agents_allowed,
            SUM(manager_ct) as total_managers_allowed
        ', [$now])
            ->first()
            ->toArray();
    }
}
