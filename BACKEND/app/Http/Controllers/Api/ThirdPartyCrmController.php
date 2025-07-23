<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ThirdPartyCrmController extends Controller
{
    public function forward(Request $request)
    {
        // $config = ThirdPartyConfiguration::first();
        // if (!$config || !$config->url || !$config->authorization) {
        //     return response()->json(['error' => 'Third-party configuration not found'], 500);
        // }
        // $response = Http::withHeaders([
        //     'Authorization' => $config->authorization,
        //     'Content-Type'  => 'application/json',
        // ])->post($config->url, $request->all());

        $apiKey = env('EXTERNAL_API_KEY');
        $apiUrl = env('EXTERNAL_API_URL');

        if (!$apiKey || !$apiUrl) {
            return response()->json([
                'message' => 'Missing API configuration in .env file.',
                'data' => []
            ], 500);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Apikey ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post($apiUrl, $request->all());

        $data = $response->json();

        if ($response->successful() && isset($data['id'])) {
            return response()->json([
                'message' => 'Request Successful',
                'data' => $data
            ], 200);
        }

        return response()->json([
            'message' => 'Request Failed',
            'data' => $data
        ], $response->status());
    }
}
