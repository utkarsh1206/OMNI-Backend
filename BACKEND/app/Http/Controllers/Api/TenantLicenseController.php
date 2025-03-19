<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantLicense;
use Illuminate\Support\Facades\DB;
use App\Services\AESService;
use App\Http\Controllers\Api\TenantLicenseUpdateController;
use App\Http\Controllers\Api\LicenseController;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Http\Requests\TenantLicenseUpdateRequest;

class TenantLicenseController extends Controller
{
    protected $licenseController;

    // Inject LicenseController via the constructor
    public function __construct(LicenseController $licenseController)
    {
        $this->licenseController = $licenseController;
    }

    /**
     * @OA\Put(
     *     path="/api/tenant-license/{id}", 
     *     tags={"Tenant License"},
     *     security={{"bearerAuth": {}}},
     *     summary="Update Tenant License",
     *     description="This endpoint is used to update the tenant's license information, including expiration date, agent count, live agent count, and manager count.",
     *     operationId="updateTenantLicense",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the tenant whose license information is to be updated",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     requestBody={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"expire_date", "agent_ct", "live_agent_ct", "manager_ct"},
     *                 @OA\Property(
     *                     property="expire_date",
     *                     type="string",
     *                     format="date",
     *                     description="The expiration date of the tenant's license"
     *                 ),
     *                 @OA\Property(
     *                     property="agent_ct",
     *                     type="integer",
     *                     description="The total number of agents allowed under the tenant's license"
     *                 ),
     *                 @OA\Property(
     *                     property="live_agent_ct",
     *                     type="integer",
     *                     description="The total number of live agents allowed under the tenant's license"
     *                 ),
     *                 @OA\Property(
     *                     property="manager_ct",
     *                     type="integer",
     *                     description="The total number of managers allowed under the tenant's license"
     *                 )
     *             )
     *         )
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated the tenant's license",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tenant license updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: Invalid data provided for the license update",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="License update failed due to invalid data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tenant not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the tenant's license")
     *         )
     *     )
     * )
     */
    public function update(TenantLicenseUpdateRequest $request, $id)
    {
        // $validatedData = $request->validate([
        //     'expire_date' => 'required',
        //     'agent_ct' => 'required|integer|min:1|max:9999',
        //     'live_agent_ct' => 'required|integer|min:1|max:9999',
        //     'manager_ct' => 'required|integer|min:1|max:9999',
        // ]);

        // // Convert expire_date to epoch time
        // $expireDateEpoch = Carbon::parse($validatedData['expire_date'])->timestamp;
        // $validatedData['expire_date'] = $expireDateEpoch;

        $validatedData = $request->validated();

        $licenseDataResponse = $this->getLicense($id, $request);
        $licenseLimits = $licenseDataResponse->getData(true);

        $applicationCode = $this->validateLicenseData($request, $licenseLimits);
        if ($applicationCode) {
            return response()->json([
                'message' => $applicationCode,
            ], ResponseHTTP::HTTP_BAD_REQUEST);
        }

        $tenant = Tenant::find($id);
        if (!$tenant) {
            return response()->json([
                'message' => trans('message.tenant_not_found')
            ], ResponseHTTP::HTTP_NOT_FOUND);
        }

        $currentDateEpoch = Carbon::now()->timestamp;

        TenantLicense::updateOrCreate(
            ['tenant_id' => $id],
            [
                'start_date' => $currentDateEpoch,
                'expire_date' => $validatedData['expire_date'],
                'agent_ct' => $validatedData['agent_ct'],
                'live_agent_ct' => $validatedData['live_agent_ct'],
                'manager_ct' => $validatedData['manager_ct'],
                'updated_at' => $currentDateEpoch,
                'created_at' => $currentDateEpoch, 
            ]
        );

        $encryptedLicense = $this->generateLicenseData($validatedData);

        $tenantLicenseUpdateController = new TenantLicenseUpdateController();
        return $tenantLicenseUpdateController->updateLicense($tenant->id, new Request([
            'license_key' => $encryptedLicense
        ]));
    }

    /**
     * @OA\Get(
     *     path="/api/tenant/{id}/license", 
     *     tags={"Tenant License"},
     *  security={{"bearerAuth": {}}},
     *     summary="Get Tenant License",
     *     description="This endpoint retrieves the tenant's license details, including the number of available agents, live agents, ACL users, and license validity.",
     *     operationId="getTenantLicense",
     *     @OA\Parameter(
     *         name="tenantId",
     *         in="path",
     *         required=true,
     *         description="The ID of the tenant whose license information is to be retrieved",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the tenant's license data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="tenant_id", type="integer", example=1),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-03-17"),
     *                 @OA\Property(property="expire_date", type="string", format="date", example="2025-12-31"),
     *                 @OA\Property(property="agent_ct", type="integer", example=100),
     *                 @OA\Property(property="live_agent_ct", type="integer", example=50),
     *                 @OA\Property(property="manager_ct", type="integer", example=10),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T10:00:00Z"),
     *                 @OA\Property(property="license_key", type="string", example="abc123xyz"),
     *                 @OA\Property(property="available_agents", type="integer", example=200),
     *                 @OA\Property(property="available_live_agents", type="integer", example=100),
     *                 @OA\Property(property="available_acl_users", type="integer", example=20),
     *                 @OA\Property(property="license_validity", type="string", format="date", example="2025-12-31"),
     *                 @OA\Property(property="database_enterprises", type="integer", example=5),
     *                 @OA\Property(property="license_expiration_in_days", type="integer", example=150)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: Invalid license data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid license data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tenant not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An error occurred while retrieving the tenant's license")
     *         )
     *     )
     * )
     */
    public function getLicense($tenantId, Request $request)
    {
        $decodedLicense = $this->licenseController->decodeLicense($request);

        $decodedLicenseData = $decodedLicense->getData(true);

        if (!$decodedLicenseData || !isset($decodedLicenseData['license_key'])) {
            return response()->json([
                'message' => trans('message.invalid_license_data')
            ], ResponseHTTP::HTTP_BAD_REQUEST);
        }

        $LICENSEvalidity_date = $decodedLicenseData['validity_date'];
        $LICENSEnumber_of_agents = $decodedLicenseData['number_of_agents'];
        $LICENSEnumber_of_live_agents = $decodedLicenseData['number_of_live_agents'];
        $LICENSEnumber_of_acl_users = $decodedLicenseData['number_of_acl_users'];

        $tenant_data = DB::table('tenant_licenses')
            ->join('tenants', 'tenant_licenses.tenant_id', '=', 'tenants.id')
            ->select(
                'tenant_licenses.tenant_id',
                'tenant_licenses.start_date',
                'tenant_licenses.expire_date',
                'tenant_licenses.agent_ct',
                'tenant_licenses.live_agent_ct',
                'tenant_licenses.manager_ct',
                'tenant_licenses.created_at',
                'tenants.license_key'
            ) // Added license_key
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
}
