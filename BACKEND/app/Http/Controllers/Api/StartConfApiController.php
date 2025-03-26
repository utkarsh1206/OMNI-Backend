<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartConfApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Validator;

class StartConfApiController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function startConfApi(Request $request)
    {
        try {
            $config = getNeoxConfig();
            // Check configuration status and handle error centrally
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            $ip = $config['neox_local_ip'];
            $neoxToken = $config['neox_token'];

            $url = "https://{$ip}/api/agent/actions/v2/agent_startconf.php";

            // Get validation rules from FormRequest
            $rules = (new StartConfApiRequest())->rules();

            // Validate request using extracted rules
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()]);
            }

            $validatedData = array_merge($validator->validated(), ['token' => $neoxToken]);
            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
