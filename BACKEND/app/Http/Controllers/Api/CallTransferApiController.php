<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallTransferApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;

class CallTransferApiController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function callTransferApi(CallTransferApiRequest $request)
    {
        try {
            $ip = getNeoxLocalIp();
            if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
                return $ip;
            }

            $url = "https://{$ip}/api/agent/actions/v2/agent_call_transfer.php";

            if ($response = validateSecretKey($request->input('token'))) {
                return $response;
            }

            $validatedData = $request->validated();
            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
