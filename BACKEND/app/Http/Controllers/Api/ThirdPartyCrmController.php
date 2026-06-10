<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\CustomApiResponse;
use App\Models\ApiConfig;
use App\Services\ApiCallerService;
use App\Services\ExternalApiTargetResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class ThirdPartyCrmController extends Controller
{
    private $apiResponse;

    public function __construct(
        CustomApiResponse $customApiResponse,
        private ExternalApiTargetResolverService $targetResolver,
        private ApiCallerService $apiCaller
    ) {
        $this->apiResponse = $customApiResponse;
    }

    public function options(Request $request)
    {
        $buttonKey = trim((string) $request->input('button_key', 'generate-crm-token'));

        if ($buttonKey === '') {
            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                null,
                'button_key is required.',
                ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $targets = $this->targetResolver->resolve($buttonKey);

        return $this->apiResponse->getResponseStructure(
            trans('message.success'),
            [
                'button_key' => $buttonKey,
                'count' => count($targets),
                'targets' => $targets,
            ],
            trans('message.data_fetch')
        );
    }

    public function forward(Request $request)
    {
        $buttonKey = trim((string) $request->input('button_key', 'generate-crm-token'));
        $targetId = $request->input('target_id');
        $payload = $request->except(['button_key', 'target_id']);

        if ($buttonKey === '') {
            return response()->json([
                'message' => 'Request Failed',
                'data' => ['error' => 'button_key is required.']
            ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
        }

        $targets = $this->targetResolver->resolve($buttonKey);

        if (empty($targets)) {
            return response()->json([
                'message' => 'Request Failed',
                'data' => ['error' => 'No API target configured for this button.']
            ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (empty($targetId)) {
            if (count($targets) === 1) {
                $targetId = $targets[0]['target_id'];
            } else {
                return response()->json([
                    'message' => 'Request Failed',
                    'data' => ['error' => 'Multiple API targets found. Please select target_id.']
                ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $target = collect($targets)->firstWhere('target_id', $targetId);

        if (!$target) {
            return response()->json([
                'message' => 'Request Failed',
                'data' => ['error' => 'Invalid target_id for the selected button_key.']
            ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (($target['source'] ?? null) === 'api_config') {
            return $this->forwardViaApiConfig($targetId, $payload);
        }

        return $this->forwardViaEnv($targetId, $payload);
    }

    private function forwardViaApiConfig(string $targetId, array $payload)
    {
        $apiConfigId = (int) str_replace('cfg:', '', $targetId);

        $apiConfig = ApiConfig::query()
            ->where('id', $apiConfigId)
            ->where('status', true)
            ->first();

        if (!$apiConfig) {
            return response()->json([
                'message' => 'Request Failed',
                'data' => ['error' => trans('message.api_config_not_found')]
            ], ResponseHTTP::HTTP_NOT_FOUND);
        }

        $response = $this->apiCaller->call(
            $apiConfig,
            override: ['body' => $payload],
            params: $payload
        );

        $status = $response['status'] ?? ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR;
        $data = $response['body'] ?? [];

        if ($status >= 200 && $status < 300) {
            return response()->json([
                'message' => 'Request Successful',
                'data' => $data
            ], ResponseHTTP::HTTP_OK);
        }

        return response()->json([
            'status' => $status,
            'message' => 'Request Failed',
            'data' => $data
        ], $status);
    }

    private function forwardViaEnv(string $targetId, array $payload)
    {
        $credentials = $this->resolveEnvCredentials($targetId);

        if (!$credentials['api_url']) {
            return response()->json([
                'message' => 'Missing API URL configuration in .env file.',
                'data' => []
            ], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }

        $headers = [
            'Content-Type'  => 'application/json',
        ];

        if (filled($credentials['api_key'])) {
            $headers['Authorization'] = 'Apikey ' . $credentials['api_key'];
        }

        $response = Http::withHeaders($headers)->post($credentials['api_url'], $payload);

        $data = $response->json();

        if ($response->successful()) {
            return response()->json([
                'message' => 'Request Successful',
                'data' => $data
            ], ResponseHTTP::HTTP_OK);
        }

        return response()->json([
            'message' => 'Request Failed',
            'data' => $data
        ], $response->status());
    }

    private function resolveEnvCredentials(string $targetId): array
    {
        if ($targetId === 'env:default') {
            return [
                'api_key' => env('EXTERNAL_API_KEY'),
                'api_url' => env('EXTERNAL_API_URL'),
            ];
        }

        $index = str_replace('env:', '', $targetId);

        if (!preg_match('/^\\d+$/', $index)) {
            return ['api_key' => null, 'api_url' => null];
        }

        return [
            'api_key' => env('EXTERNAL_API_KEY_' . $index),
            'api_url' => env('EXTERNAL_API_URL_' . $index),
        ];
    }
}
