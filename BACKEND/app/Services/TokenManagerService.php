<?php

namespace App\Services;

use App\Models\AuthProvider;
use App\Models\ApiToken;

class TokenManagerService
{
    public function __construct(
        protected HttpClientService $httpClient
    ) {}

    public function getValidToken(AuthProvider $provider): string
    {
        if (!$provider->shouldRefreshToken()) {
            return $provider->token->access_token;
        }

        return $this->refreshToken($provider);
    }

    public function refreshToken(AuthProvider $provider): string
    {
        $config = $provider->request_config ?? [];

        $response = $this->httpClient->send(
            method: $provider->method ?? 'POST',
            url: $provider->token_url,
            headers: $config['headers'] ?? [],
            query: $config['query'] ?? [],
            body: $config['body'] ?? []
        );

        $mapped = $provider->extractFromResponse($response['body']);

        $expiresAt = $provider->resolveExpiry($mapped);

        $token = ApiToken::updateOrCreate(
            ['auth_provider_id' => $provider->id],
            [
                'access_token' => $mapped['access_token'],
                'refresh_token' => $mapped['refresh_token'] ?? null,
                'expires_at' => $expiresAt,
                'meta' => $response['body'],
            ]
        );

        return $token->access_token;
    }
}
