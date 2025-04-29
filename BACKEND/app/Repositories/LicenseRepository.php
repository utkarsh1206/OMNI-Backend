<?php

namespace App\Repositories;

use App\Models\Admins;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Auth;
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
            $license = Admins::find($userId);
            $license->license_key = $request->license_key;
            $license->save();

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
