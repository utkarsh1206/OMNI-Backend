<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantLicense;
use App\Models\Admins;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\AESService;
use App\Http\Controllers\Api\TenantLicenseUpdateController;
use App\Http\Controllers\Api\LicenseController;
use Carbon\Carbon;

class TenantLicenseController extends Controller
{
    protected $licenseController;

    // Inject LicenseController via the constructor
    public function __construct(LicenseController $licenseController)
    {
        $this->licenseController = $licenseController;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'expire_date' => 'required',
            'agent_ct' => 'required|integer|min:1|max:9999',
            'live_agent_ct' => 'required|integer|min:1|max:9999',
            'manager_ct' => 'required|integer|min:1|max:9999',
        ]);

        // Convert expire_date to epoch time
        $expireDateEpoch = Carbon::parse($validatedData['expire_date'])->timestamp;
        $validatedData['expire_date'] = $expireDateEpoch;

        $licenseDataResponse = $this->getLicense($id, $request);
        $licenseLimits = $licenseDataResponse->getData(true);

        $applicationCode = $this->validateLicenseData($request, $licenseLimits);
        if ($applicationCode) {
            return response()->json([
                'message' => $applicationCode,
            ], 400);
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found'
            ], 404);
        }

        $license = TenantLicense::where('tenant_id', $id)->first();
        $currentDateEpoch = Carbon::now()->timestamp;

        if ($license) {
            $license->update([
                'start_date' => $currentDateEpoch,
                'expire_date' => $validatedData['expire_date'],
                'agent_ct' => $validatedData['agent_ct'],
                'live_agent_ct' => $validatedData['live_agent_ct'],
                'manager_ct' => $validatedData['manager_ct'],
                'updated_at' => $currentDateEpoch
            ]);
        } else {
            TenantLicense::create([
                'tenant_id' => $id,
                'start_date' => $currentDateEpoch,
                'expire_date' => $validatedData['expire_date'],
                'agent_ct' => $validatedData['agent_ct'],
                'live_agent_ct' => $validatedData['live_agent_ct'],
                'manager_ct' => $validatedData['manager_ct'],
                'created_at' => $currentDateEpoch
            ]);
        }

        $encryptedLicense = $this->generateLicenseData($validatedData);

        $tenantLicenseUpdateController = new TenantLicenseUpdateController();
        return $tenantLicenseUpdateController->updateLicense($tenant->id, new Request([
            'license_key' => $encryptedLicense
        ]));
    }

    private function validateLicenseData(Request $request, $licenseLimits)
    {
        if (empty($request->expire_date)) {
            return "Expiry date is required.";
        }

        $expireDateEpoch = Carbon::parse($request->expire_date)->timestamp;
        $currentDateEpoch = Carbon::now()->startOfDay()->timestamp;
        $licenseValidityEpoch = Carbon::parse($licenseLimits['data']['license_validity'])->timestamp;

        if ($expireDateEpoch <= $currentDateEpoch) {
            return 'Expiry date must be greater than today.';
        }

        if ($expireDateEpoch >= $licenseValidityEpoch) {
            return "Expiry date should not be greater than the license validity date.";
        }


        // Validate agent_ct against dynamic license limits
        if (empty($request->agent_ct) || !is_numeric($request->agent_ct) || !preg_match("/^[\d]{1,5}+$/", $request->agent_ct)) {
            return "Agent count not valid";
        }
        if ($request->agent_ct > $licenseLimits['data']['available_agents']) {
            return "Agent count is not greater than limit";
        }

        // Validate live_agent_ct against dynamic license limits
        if (empty($request->live_agent_ct) || !is_numeric($request->live_agent_ct) || !preg_match("/^[\d]{1,5}+$/", $request->live_agent_ct)) {
            return "Live agent count not valid";
        }
        if ($request->live_agent_ct > $licenseLimits['data']['available_live_agents']) {
            return "Live agent count is not greater than limit";
        }

        // Validate manager_ct against dynamic license limits
        if (empty($request->manager_ct) || !is_numeric($request->manager_ct) || !preg_match("/^[\d]{1,5}+$/", $request->manager_ct)) {
            return "Manager count not valid";
        }
        if ($request->manager_ct > $licenseLimits['data']['available_acl_users']) {
            return "Manager count is not greater than limit";
        }
    }

    private function generateLicenseData($validatedData)
    {
        $inputKey = exec('/usr/bin/hostid');
        $blockSize = 256;
        $separator = '##$@@$##';
        $dc_license = '';

        $aes = new AESService(null, $inputKey, $blockSize, AESService::M_CBC);

        $aes->setData($validatedData['expire_date']);
        $dc_license .= $aes->encrypt() . $separator;

        $aes->setData($validatedData['agent_ct']);
        $dc_license .= $aes->encrypt() . $separator;

        $aes->setData($validatedData['live_agent_ct']);
        $dc_license .= $aes->encrypt() . $separator;

        $aes->setData($validatedData['manager_ct']);
        $dc_license .= $aes->encrypt();

        return $dc_license;
    }

    public function getLicense($tenantId, Request $request)
    {
        $decodedLicense = $this->licenseController->decodeLicense($request);

        $decodedLicenseData = $decodedLicense->getData(true);

        if (!$decodedLicenseData || !isset($decodedLicenseData['license_key'])) {
            return response()->json([
                'message' => 'Invalid license data'
            ], 400);
        }

        $LICENSEvalidity_date = $decodedLicenseData['validity_date'];
        $LICENSEnumber_of_agents = $decodedLicenseData['number_of_agents'];
        $LICENSEnumber_of_live_agents = $decodedLicenseData['number_of_live_agents'];
        $LICENSEnumber_of_acl_users = $decodedLicenseData['number_of_acl_users'];

        $tenant_data = DB::table('tenant_licenses')
        ->join('tenants', 'tenant_licenses.tenant_id', '=', 'tenants.id')
        ->select('tenant_licenses.tenant_id', 'tenant_licenses.start_date', 'tenant_licenses.expire_date', 
                 'tenant_licenses.agent_ct', 'tenant_licenses.live_agent_ct', 'tenant_licenses.manager_ct', 
                 'tenant_licenses.created_at', 'tenants.license_key') // Added license_key
        ->where('tenant_licenses.tenant_id', $tenantId)
        ->first();

        $tenantDetails = [];
        if ($tenant_data) {
            $tenantDetails = [
                'tenant_id' => $tenant_data->tenant_id,
                'start_date' => $tenant_data->start_date,
                'expire_date' => $tenant_data->expire_date ? Carbon::createFromTimestamp($tenant_data->expire_date)->format('Y-m-d') : null,
                'agent_ct' => $tenant_data->agent_ct,
                'live_agent_ct' => $tenant_data->live_agent_ct,
                'manager_ct' => $tenant_data->manager_ct,
                'created_at' => $tenant_data->created_at,
                'license_key' => $tenant_data->license_key,
            ];
        }


        $DBnumber_of_enterprise = DB::table('details')->count();

        $tenant_data_count = DB::table('tenant_licenses')
            ->join('tenants', 'tenants.id', '=', 'tenant_licenses.tenant_id')
            ->selectRaw('SUM(agent_ct) as agent, SUM(live_agent_ct) as live_agent, SUM(manager_ct) as manager')
            // ->where('tenant_id', '!=', $tenantId)
            ->first();

        $DBnumber_of_agents = $tenant_data_count->agent ?? 0;
        $DBnumber_of_live_agents = $tenant_data_count->live_agent ?? 0;
        $DBnumber_of_acl_users = $tenant_data_count->manager ?? 0;

        $current_date = Carbon::now();
        $license_date = Carbon::parse($LICENSEvalidity_date);
        $license_expiration_in_days = $current_date->diffInDays($license_date, false);

        $available_number_of_agents = $LICENSEnumber_of_agents - $DBnumber_of_agents;
        $available_number_of_live_agents = $LICENSEnumber_of_live_agents - $DBnumber_of_live_agents;
        $available_number_of_acl_users = $LICENSEnumber_of_acl_users - $DBnumber_of_acl_users;

        return response()->json([
            'message' => 'Success',
            'data' => array_merge($tenantDetails, [
                'available_agents' => $available_number_of_agents,
                'available_live_agents' => $available_number_of_live_agents,
                'available_acl_users' => $available_number_of_acl_users,
                'license_validity' => $LICENSEvalidity_date,
                'database_enterprises' => $DBnumber_of_enterprise,
                'license_expiration_in_days' => $license_expiration_in_days,
            ]),
        ]);
    }
}
