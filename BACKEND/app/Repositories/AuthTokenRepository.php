<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\AuthToken;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
Use App\Models\Detail;
use Illuminate\Support\Facades\DB;
class AuthTokenRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            getDbConnection($request->id);
            
            $checkToken = AuthToken::select('token')->where('expire_at', '>', Carbon::now())
            ->first();

            if ($checkToken) {
                $authToken = $checkToken;
            //    $authToken->status = "Existing";
            } else {
                $authToken = AuthToken::create([
                    'token' =>  Str::random(64),
                    'ipaddress' => $request->ip(),
                    'expire_at' =>  Carbon::now()->addHours(3),
                ]);
            //    $authToken->status = "New";
            }
           
            return $this->apiResponse->getResponseStructure(trans('message.success'), $authToken, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Auth Token Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

}
