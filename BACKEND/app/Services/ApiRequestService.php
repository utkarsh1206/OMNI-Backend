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
    public function handleApiRequest(Request $request, $action, $formRequestClass)
    {
        try {
            // Fetch configuration
            $config = getNeoxConfig();

            // Check configuration status and handle error centrally
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            // Extract IP and Token from the configuration
            $ip = $config['neox_local_ip'];
            $neoxToken = $config['neox_token'];

            // Build the URL dynamically based on the action
            $url = "https://{$ip}/api/agent/actions/v2/{$action}.php";

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
