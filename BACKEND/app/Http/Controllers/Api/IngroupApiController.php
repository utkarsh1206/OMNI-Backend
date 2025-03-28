<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\IngroupListRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IngroupApiController extends Controller
{
    public function ingroupListApi(Request $request)
    {
        // Check if JWT is not provided
        if ($request->isJWT == 'No') {
            $domainName = $request->domain_name;
            if (!$domainName) {
                return response()->json(['error' => 'Domain name is required']);
            }
        } else {
            // If JWT is present, domain name is not required
            $domainName = null;
        }

        // Retrieve configuration
        $config = getNeoxConfig($domainName);

        // If there is an error in the config, return the error response
        if (isset($config['status']) && $config['status'] === 'error') {
            return response()->json($config);
        }

        $ip = $config['neox_local_ip'];
        $url = "https://{$ip}/api/admin/actions/v2/ingroup_list.php";

        // Get validation rules from FormRequest
        $rules = (new IngroupListRequest())->rules();

        // Validate request using extracted rules
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $validatedData = $validator->validated();
        return sendHttpRequest($url, 'POST', $validatedData);
    }
}
