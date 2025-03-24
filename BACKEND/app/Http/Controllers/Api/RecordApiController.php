<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;

class RecordApiController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function callRecordingApi(RecordApiRequest $request)
    {
        try {
            $ip = getNeoxLocalIp();
            if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
                return $ip;
            }

            $url = "https://{$ip}/api/agent/actions/v2/agent_record.php";

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
