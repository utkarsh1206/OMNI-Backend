<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AESService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class LicenseController extends Controller
{
    protected $aesService;

    // Constructor to inject AESService
    public function __construct(AESService $aesService)
    {
        $this->aesService = $aesService;
    }

    /**
     * @OA\Get(
     *     path="/api/encode-license", 
     *     tags={"License"},
     *     security={{"apiAuth": {}}}, 
     *     summary="Encode License Data",
     *     description="This endpoint is used to encode license data such as validity date, number of agents, number of live agents, and number of ACL users.",
     *     operationId="encodeLicense",
     *     requestBody={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"validity_date", "number_of_agents", "number_of_live_agents"},
     *                 @OA\Property(
     *                     property="validity_date",
     *                     type="string",
     *                     format="date",
     *                     description="The validity date of the license"
     *                 ),
     *                 @OA\Property(
     *                     property="number_of_agents",
     *                     type="integer",
     *                     description="The total number of agents"
     *                 ),
     *                 @OA\Property(
     *                     property="number_of_live_agents",
     *                     type="integer",
     *                     description="The total number of live agents"
     *                 ),
     *                 @OA\Property(
     *                     property="number_of_acl_users",
     *                     type="integer",
     *                     default=0,
     *                     description="The number of ACL users (default to 0 if not provided)"
     *                 )
     *             )
     *         )
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successfully encoded the license key",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="License key encoded successfully"),
     *             @OA\Property(property="license_key", type="string", example="encrypted_license_key")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Encryption failed: <error_message>")
     *         )
     *     )
     * )
     */
    public function encodeLicense(Request $request)
    {
        // Assuming the request has the data for the license
        $validityDate = $request->input('validity_date');
        $numberOfAgents = $request->input('number_of_agents');
        $numberOfLiveAgents = $request->input('number_of_live_agents');
        $numberOfAclUsers = $request->input('number_of_acl_users', 0); // Default to 0 if not provided

        // Get the host ID (which is used as the encryption key)
        $inputKey = exec('/usr/bin/hostid');

        // Encrypt the data
        try {
            $EvalidityDate = $this->aesService->encryptData($validityDate, $inputKey);
            $EnumberOfAgents = $this->aesService->encryptData($numberOfAgents, $inputKey);
            $EnumberOfLiveAgents = $this->aesService->encryptData($numberOfLiveAgents, $inputKey);
            $EnumberOfAclUsers = $this->aesService->encryptData($numberOfAclUsers, $inputKey);

            // Join the encrypted fields using the separator
            $separator = '##$@@$##';
            $licenseKey = $EvalidityDate . $separator . $EnumberOfAgents . $separator . $EnumberOfLiveAgents . $separator . $EnumberOfAclUsers;

            return response()->json(['message' => trans('message.license_key_encoded'), 'license_key' => $licenseKey]);
        } catch (\Exception $e) {
            return response()->json(['error' => trans('message.encryption_failed') . $e->getMessage()], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/decode-license", 
     *     tags={"License"},
     *     security={{"apiAuth": {}}}, 
     *     summary="Decode License Data",
     *     description="This endpoint is used to decode license data, returning the details of the license such as validity date, number of agents, number of live agents, and number of ACL users. The license key is decrypted using the host ID.",
     *     operationId="decodeLicense",
     *     requestBody={
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"token"},
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     description="Bearer token used for JWT authentication"
     *                 ),
     *                 @OA\Property(
     *                     property="is_tanant",
     *                     type="boolean",
     *                     description="Flag to indicate whether the user is a tenant (optional, default is false)"
     *                 )
     *             )
     *         )
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successfully decoded the license key",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="license_key", type="string", example="encoded_license_key"),
     *             @OA\Property(property="validity_date", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="number_of_agents", type="integer", example=100),
     *             @OA\Property(property="number_of_live_agents", type="integer", example=80),
     *             @OA\Property(property="number_of_acl_users", type="integer", example=50),
     *             @OA\Property(property="number_of_enterprise", type="integer", example=1, nullable=true),
     *             @OA\Property(property="is_tanant", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid license format",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid license format")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="License key not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="License key not found"),
     *             @OA\Property(property="adminFlag", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Decryption failed: <error_message>")
     *         )
     *     )
     * )
     */
    public function decodeLicense(Request $request)
    {
        $isTenant = $request->input('is_tanant', false);

        $token = $request->bearerToken();
        if ($token != "") {
            $payload = JWTAuth::setToken($token)->getPayload();

            if ($payload['role_id'] > 0) {
                $isTenant = true;
            }
        }

        $licenseKey = $isTenant
        ? DB::table('licenses')->value('license_key')
        : DB::table('admins')->value('license_key');

        $adminFlag = $isTenant ? 0 : 1;
        
        if (!$licenseKey) {
            return response()->json([
                'error' => trans('message.license_key_not_found'),
                'adminFlag' => $adminFlag
            ], ResponseHTTP::HTTP_NOT_FOUND);
        }

        // if (!$licenseKey) {
        //     return response()->json(['error' => 'License key not found'], 404);
        // }

        $inputKey = exec('/usr/bin/hostid');
        $seprator = '##$@@$##';
        $param = explode($seprator, $licenseKey);

        if (count($param) < 4) {
            return response()->json(['error' => trans('message.invalid_license_format')], ResponseHTTP::HTTP_BAD_REQUEST);
        }

        if (!$isTenant) {
            $EvalidityDate = $param[0];
            $EnumberOfEnterprise = $param[1];
            $EnumberOfAgents = $param[2];
            $EnumberOfLiveAgents = $param[3];
            $EnumberOfAclUsers = $param[4];
        } else {
            $EvalidityDate = $param[0];
            $EnumberOfAgents = $param[1];
            $EnumberOfLiveAgents = $param[2];
            $EnumberOfAclUsers = $param[3];
        }

        try {
            $decodedData = [
                'license_key' => $licenseKey,
                'validity_date' => $this->aesService->decryptData($EvalidityDate, $inputKey),
                // 'number_of_enterprise' => $this->aesService->decryptData($EnumberOfEnterprise, $inputKey),
                'number_of_agents' => $this->aesService->decryptData($EnumberOfAgents, $inputKey),
                'number_of_live_agents' => $this->aesService->decryptData($EnumberOfLiveAgents, $inputKey),
                'number_of_acl_users' => $EnumberOfAclUsers ? $this->aesService->decryptData($EnumberOfAclUsers, $inputKey) : 0,
            ];

            if (!$isTenant && $EnumberOfEnterprise) {
                $decodedData['number_of_enterprise'] = $this->aesService->decryptData($EnumberOfEnterprise, $inputKey);
            }

            if ($isTenant) {
                $decodedData['validity_date'] =  Carbon::createFromTimestamp($decodedData['validity_date'])->format('Y-m-d');
            }

            if ($token != "") {
                $decodedData['is_tanant'] = $isTenant;
            }

            return response()->json($decodedData);
        } catch (\Exception $e) {
            return response()->json(['error' => trans('message.decryption_failed') . $e->getMessage()], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
