<?php

namespace App\Services;

use App\Models\AuthProvider;
use App\Models\ApiToken;
use RuntimeException;

class TokenManagerService
{
    public function __construct(
        protected HttpClientService $httpClient
    ) {}

    public function getValidToken(AuthProvider $provider): string
    {
        if (!$provider->shouldRefreshToken()) {
            $cachedToken = $provider->token?->access_token;

            if (is_string($cachedToken) && trim($cachedToken) !== '') {
                return $cachedToken;
            }
        }

        return $this->refreshToken($provider);
    }

    public function refreshToken(AuthProvider $provider): string
    {
        if (empty($provider->token_url)) {
            throw new RuntimeException('Token URL is not configured for this auth provider.');
        }

        $config = $provider->request_config ?? [];

        $response = $this->httpClient->send(
            method: $provider->method ?? 'POST',
            url: $provider->token_url,
            headers: $config['headers'] ?? [],
            query: $config['query'] ?? [],
            body: $config['body'] ?? []
        );

        $mapped = $provider->extractFromResponse($response['body']);

        $accessToken = $mapped['access_token'] ?? null;

        if (!is_string($accessToken) || trim($accessToken) === '') {
            throw new RuntimeException('Unable to resolve access token from provider response. Check response_mapping.access_token.');
        }

        $expiresAt = $provider->resolveExpiry($mapped);

        $token = ApiToken::updateOrCreate(
            ['auth_provider_id' => $provider->id],
            [
                'access_token' => $accessToken,
                'refresh_token' => $mapped['refresh_token'] ?? null,
                'expires_at' => $expiresAt,
                'meta' => $response['body'],
            ]
        );

        return $accessToken;
    }
}
