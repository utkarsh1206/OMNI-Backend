<?php

namespace App\Services;

use App\Http\Requests\DispositionListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DispositionListService
{
    public function getDispositions(Request $request)
    {
        $campaign_id = $request->input('campaign_id');

        $config = getNeoxConfig();

        if (isset($config['status']) && $config['status'] === 'error') {
            throw new \Exception($config['message'] ?? 'Configuration error');
        }

        $ip = $config['neox_local_ip'] ?? null;
        if (!$ip) {
            throw new \Exception(trans('message.local_ip_not_found'));
        }

        $url = "https://{$ip}/api/admin/actions/v2/disposition_list.php";

        // Get validation rules from FormRequest
        $rules = (new DispositionListRequest())->rules();

        // Validate request using extracted rules
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $validatedData = $validator->validated();
        
        $response = sendHttpRequest($url, 'POST', $validatedData);

        $responseData = $response->getData(true);

        generateLogs("info", "Response " . json_encode($responseData));
        return $responseData['data'] ?? [];
    }
}
