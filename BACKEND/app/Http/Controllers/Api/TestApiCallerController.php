<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiConfig;
use App\Services\ApiCallerService;
use Illuminate\Http\Request;

class TestApiCallerController extends Controller
{
    public function __construct(
        protected ApiCallerService $apiCaller
    ) {}

    /**
     * POST /api/test-caller/execute
     * 
     * Body: {
     *   "api_config_id": 1,
     *   "overrides": {
     *     "headers": {},
     *     "query": {},
     *     "body": {}
     *   }
     * }
     */
    public function execute(Request $request)
    {
        $request->validate([
            'api_config_id' => 'required|exists:api_configs,id',
            'overrides' => 'nullable|array'
        ]);

        $apiConfig = ApiConfig::findOrFail($request->api_config_id);
        $overrides = $request->input('overrides', []);

        try {
            $response = $this->apiCaller->call($apiConfig, $overrides);

            return response()->json([
                'api_config' => $apiConfig->name,
                'url' => $apiConfig->getFullUrl(),
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
