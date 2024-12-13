<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AESService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LicenseController extends Controller
{
    protected $aesService;

    // Constructor to inject AESService
    public function __construct(AESService $aesService)
    {
        $this->aesService = $aesService;
    }

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

            return response()->json(['message' => 'License key encoded successfully', 'license_key' => $licenseKey]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Encryption failed: ' . $e->getMessage()], 500);
        }
    }

    public function decodeLicense(Request $request)
    {
        $isTenant = $request->has('is_tanant') ? $request->input('is_tanant') : true;
        $token = $request->bearerToken();
        if ($token != "") {
            $payload = JWTAuth::setToken($token)->getPayload();

            if ($payload['role_id'] > 0)
            {
                $isTenant = false;
            }
        }

        if ($isTenant) {
            $licenseKey = DB::table('admins')->value('license_key');
        } else {
            $licenseKey = DB::table('licenses')->value('license_key');
        }

        if (!$licenseKey) {
            return response()->json(['error' => 'License key not found'], 404);
        }

        $inputKey = exec('/usr/bin/hostid');
        $blockSize = 256;
        $seprator = '##$@@$##';
        $param = explode($seprator, $licenseKey);

        if (count($param) < 4) {
            return response()->json(['error' => 'Invalid license format'], 400);
        }

        if ($isTenant) {
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

            if ($isTenant && $EnumberOfEnterprise) {
                $decodedData['number_of_enterprise'] = $this->aesService->decryptData($EnumberOfEnterprise, $inputKey);
            }

            if ($token != "") {
                $decodedData['is_tanant'] = $isTenant;
            }

            return response()->json($decodedData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Decryption failed: ' . $e->getMessage()], 500);
        }
    }
}
