<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiConfigRequest;
use App\Models\ApiConfig;

class ApiConfigController extends Controller
{
    public function store(ApiConfigRequest $request)
    {
        $data = $request->validated();

        // 🔄 Parse inline auth input (your new UX feature)
        if (!empty($data['auth_input']) && $data['auth_type'] !== 'none') {
            $data['auth_config'] = [
                'location' => $data['auth_location'],
                'params' => $this->parseKeyValueInput($data['auth_input'])
            ];
        }

        unset($data['auth_input'], $data['auth_location']);

        $api = ApiConfig::create($data);

        return response()->json($api);
    }

    private function parseKeyValueInput(string $input): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($input));
        $result = [];

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
            } elseif (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
            } else {
                continue;
            }

            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    public function update(ApiConfigRequest $request, ApiConfig $apiConfig)
    {
        $data = $request->validated();

        // 🔐 Handle inline auth parsing
        if (!empty($data['auth_input']) && $data['auth_type'] !== 'none') {
            $data['auth_config'] = [
                'location' => $data['auth_location'],
                // 'params' => app(\App\Support\KeyValueParser::class)
                // ->parse($data['auth_input'])
                'params' => $this->parseKeyValueInput($data['auth_input']),
            ];
        }

        unset($data['auth_input'], $data['auth_location']);

        // If switching to auth_provider → clear inline auth
        if (!empty($data['auth_provider_id'])) {
            $data['auth_type'] = 'none';
            $data['auth_config'] = null;
        }

        // If switching to inline auth → remove provider
        if (!empty($data['auth_type']) && $data['auth_type'] !== 'none') {
            $data['auth_provider_id'] = null;
        }

        $apiConfig->update($data);

        return response()->json($apiConfig->fresh());
    }

    public function index()
    {
        $apis = ApiConfig::with('authProvider')
            ->latest()
            ->get();

        return response()->json($apis);
    }

    public function show(ApiConfig $apiConfig)
    {
        return response()->json(
            $apiConfig->load('authProvider')
        );
    }

    public function destroy(ApiConfig $apiConfig)
    {
        if ($apiConfig->status) {
            return response()->json([
                'message' => 'Cannot delete an active API. Disable it first.'
            ], 422);
        }

        $apiConfig->delete();

        return response()->json([
            'message' => 'API Config deleted successfully'
        ]);
    }
}
