<?php

namespace App\Repositories;

use App\Models\Admins;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class LicenseRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function updateLicense($request)
    {
        try {
            $userId = Auth::user()->id;
            $license = Admins::where('id', $userId)->find();
            $license->license_key = $request->license_key;
            $license->save();
            logMessage("debug","License updated", [
                'user_id' => $userId,
                'license_key' => $license->license_key
            ]);

            return response()->json([
                'code' => 0,
                'message' => trans('message.license_updated'),
            ], ResponseHTTP::HTTP_OK);
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating failed Update License : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
