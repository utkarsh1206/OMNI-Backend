<?php

namespace App\Services;

use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ApiRequestService
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }
    public function handleApiRequest(Request $request, $action, $formRequestClass, $domainName = null)
    {
        try {
            // Fetch configuration based on domain or use default if domain is not provided
            $config = $domainName ? getNeoxConfig($domainName) : getNeoxConfig();

            // Check configuration status and handle error centrally
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            // Extract IP and Token from the configuration
            $ip = $config['neox_local_ip'];

            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $apiUser = auth()->user();
                $neoxToken = getTenantUin($apiUser);
            } else {
                $neoxToken = $config['neox_token'];
            }

            // NOC-996, TODO DC Hosted, START
            $url_req_param = "";
            $jWTtoken = $request->bearerToken() ?: $request->auth_token;
            if (config('omni.IS_DC_MULTITENANT') == 1 && $jWTtoken) {
                $payload = $jWTtoken ? extractPayload($jWTtoken) : null;
                $url_req_param = "?domain=" . $payload['domain'];
            }
            // NOC-996, END

            // Handle case where IP or token is missing
            if (!$ip || !$neoxToken) {
                return response()->json(['error' => 'Missing required configuration']);
            }

            // NOC-996, TODO DC Hosted, Added request parameter domain ie. $url_req_param.
            // Build the URL dynamically based on the action
            $url = "https://{$ip}/api/agent/actions/v2/{$action}.php$url_req_param";

            // Get validation rules from FormRequest
            $rules = (new $formRequestClass())->rules();

            // Validate the request using the rules
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()]);
            }

            // Merge validated data with token
            $validatedData = array_merge($validator->validated(), ['token' => $neoxToken]);

            // Send HTTP request
            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            // Handle exception and return a proper response
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
