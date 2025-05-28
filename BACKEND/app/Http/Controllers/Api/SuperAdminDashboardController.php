<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantLicense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $notDeleted = Tenant::whereNull('deleted_at')->count();

        // Call the licensesExpiringAfter10Days method here and get data
        $licensesExpiring = $this->getLicensesExpiringAfter10Days();

        return response()->json([
            'total_tenant' => $notDeleted,
            'licenses_expiring_after_10_days' => $licensesExpiring,
        ]);
    }

    protected function getLicensesExpiringAfter10Days()
    {
        $today = Carbon::now()->timestamp;
        $tenDaysLater = Carbon::now()->addDays(10)->timestamp;

        $licenses = TenantLicense::whereBetween('expire_date', [$today, $tenDaysLater])
            ->get(['tenant_id', 'expire_date']);

        return $licenses->map(function ($license) {
            return [
                'tenant_id'   => $license->tenant_id,
                'expire_date' => Carbon::createFromTimestamp($license->expire_date)->format('d/m/Y'),
            ];
        });
    }
}
