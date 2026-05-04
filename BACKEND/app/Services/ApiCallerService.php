<?php

namespace App\Services;

use App\Models\ApiConfig;
use App\Models\ApiLog;
use Illuminate\Support\Str;


class ApiCallerService
{
    public function __construct(
        protected AuthResolverService $authResolver,
        protected HttpClientService $httpClient
    ) {}

    public function call(ApiConfig $apiConfig, array $override = []): array
    {
        $headers = $apiConfig->buildHeaders();
        $query   = $apiConfig->buildQuery();
        $body    = $apiConfig->buildBody();

        // Apply overrides (optional runtime data)
        $headers = array_merge($headers, $override['headers'] ?? []);
        $query   = array_merge($query, $override['query'] ?? []);
        $body    = array_merge($body, $override['body'] ?? []);

        // Resolve Authentication
        $authData = $this->authResolver->resolve($apiConfig, $headers, $query, $body);

        $headers = $authData['headers'];
        $query   = $authData['query'];
        $body    = $authData['body'];

        $url = $apiConfig->getFullUrl();

        // Call API
        $response = $this->httpClient->send(
            method: $apiConfig->method,
            url: $url,
            headers: $headers,
            query: $query,
            body: $body,
            timeout: $apiConfig->timeout,
            retries: $apiConfig->retry_count
        );

        // Handle token expiry retry (if needed)
        if ($apiConfig->usesAuthProvider()) {
            $authProvider = $apiConfig->authProvider;

            if ($authProvider->isTokenExpiredFromResponse(
                $response['body'],
                $response['status']
            )) {
                // force refresh
                app(TokenManagerService::class)->refreshToken($authProvider);

                // retry once
                return $this->call($apiConfig, $override);
            }
        }

        // Log
        $errorMessage = $response['error'] ?? null;
        ApiLog::create([
            'api_config_id' => $apiConfig->id,
            'request' => compact('headers', 'query', 'body'),
            'response' => $response['body'],
            'status_code' => $response['status'],
            'success' => $response['status'] >= 200 && $response['status'] < 300,
            'error_message' => Str::limit($errorMessage, 1000),
        ]);

        return $response;
    }
}
