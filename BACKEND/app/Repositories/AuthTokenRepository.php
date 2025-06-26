<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\AuthToken;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AuthTokenRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request, $detail)
    {
        try {
            getDbConnection($detail->domain, true);

            $checkToken = AuthToken::select('token', 'expire_at')->where('expire_at', '>', Carbon::now())
                ->first();

            if ($checkToken) {
                $authToken = $checkToken->token;
                $expireAt = $checkToken->expire_at;
            } else {
                $authToken = Str::random(64);
                $expireAt = Carbon::now()->addHours(3);

                AuthToken::create([
                    'token' =>  $authToken,
                    'ipaddress' => $request->ip(),
                    'expire_at' =>  $expireAt,
                ]);
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                [
                    'token' => $authToken,
                    'code' => 200,
                    'expire_at' => $expireAt->toDateTimeString()
                ],
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Auth Token Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
