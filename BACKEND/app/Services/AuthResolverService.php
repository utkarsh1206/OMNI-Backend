<?php

namespace App\Services;

use App\Models\ApiConfig;

class AuthResolverService
{
    public function __construct(
        protected TokenManagerService $tokenManager
    ) {}

    public function resolve(ApiConfig $api, array $headers, array $query, array $body): array
    {
        // Priority: auth_provider
        if ($api->usesAuthProvider()) {
            return $this->resolveProvider($api, $headers, $query, $body);
        }

        // Inline auth
        if ($api->usesInlineAuth()) {
            return $this->resolveInline($api, $headers, $query, $body);
        }

        return compact('headers', 'query', 'body');
    }

    protected function resolveInline(ApiConfig $api, $headers, $query, $body): array
    {
        $location = $api->getInlineAuthLocation();
        $params   = $api->getInlineAuthParams();

        if ($api->auth_type === 'basic') {
            $username = $params['username'] ?? '';
            $password = $params['password'] ?? '';

            $headers['Authorization'] = 'Basic ' . base64_encode("$username:$password");
            return compact('headers', 'query', 'body');
        }

        if ($api->auth_type === 'api_key') {
            match ($location) {
                'header' => $headers = array_merge($headers, $params),
                'query'  => $query   = array_merge($query, $params),
                'body'   => $body    = array_merge($body, $params),
            };
        }

        return compact('headers', 'query', 'body');
    }

    protected function resolveProvider(ApiConfig $api, $headers, $query, $body): array
    {
        $provider = $api->authProvider;

        if ($provider->isTokenBased()) {
            $token = data_get($provider->credentials, 'access_token');

            if (!is_string($token) || trim($token) === '') {
                $token = $this->tokenManager->getValidToken($provider);
            }

            $headers['Authorization'] = 'Bearer ' . $token;
        }

        if ($provider->isApiKey()) {
            $credentials = $provider->credentials;

            $key   = $credentials['key_name'];
            $value = $credentials['api_key'];
            $loc   = $credentials['location'];

            match ($loc) {
                'header' => $headers[$key] = $value,
                'query'  => $query[$key] = $value,
                'body'   => $body[$key] = $value,
            };
        }

        return compact('headers', 'query', 'body');
    }
}
