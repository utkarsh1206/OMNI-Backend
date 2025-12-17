<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\AuthToken;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

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

            $currentIp = $request->ip();

            $checkToken = AuthToken::select('token', 'expire_at')
                ->where('expire_at', '>', Carbon::now())
                ->where('ipaddress', $currentIp)
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

            $authToken = Crypt::encryptString($authToken.'+'.$detail->domain);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                [
                    'token' => $authToken,
                    'code' => ResponseHTTP::HTTP_OK,
                    'expire_at' => $expireAt,
                ],
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Auth Token Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
