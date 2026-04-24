<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthProvider extends Model
{
    protected $fillable = [
        'name',
        'type',
        'token_url',
        'method',
        'credentials',
        'request_config',
        'response_mapping',
        'expiry_type',
        'token_expiry_rules',
        'token_cache_enabled',
        'get_on_each_request',
    ];

    protected $casts = [
        'credentials' => 'array',
        'request_config' => 'array',
        'response_mapping' => 'array',
        'token_expiry_rules' => 'array',
        'token_cache_enabled' => 'boolean',
        'get_on_each_request' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function token(): HasOne
    {
        return $this->hasOne(ApiToken::class);
    }

    public function apiConfigs(): HasMany
    {
        return $this->hasMany(ApiConfig::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isTokenBased(): bool
    {
        return in_array($this->type, ['bearer_token', 'oauth2']);
    }

    public function isApiKey(): bool
    {
        return $this->type === 'api_key';
    }

    public function usesHmac(): bool
    {
        return $this->type === 'hmac';
    }

    public function usesJwt(): bool
    {
        return $this->type === 'jwt';
    }

    public function shouldRefreshToken(): bool
    {
        if (!$this->token_cache_enabled) {
            return true;
        }

        if ($this->get_on_each_request) {
            return true;
        }

        $token = $this->token;

        if (!$token || !$token->access_token) {
            return true;
        }

        if ($token->expires_at && now()->gte($token->expires_at)) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Mapping Helpers
    |--------------------------------------------------------------------------
    */

    public function extractFromResponse(array $response): array
    {
        $mapping = $this->response_mapping ?? [];

        return [
            'access_token' => data_get($response, $mapping['access_token'] ?? null),
            'refresh_token' => data_get($response, $mapping['refresh_token'] ?? null),
            'expires_in'   => data_get($response, $mapping['expires_in'] ?? null),
            'expires_at'   => data_get($response, $mapping['expires_at'] ?? null),
        ];
    }

    public function resolveExpiry(array $data): ?\Carbon\Carbon
    {
        if ($this->expiry_type === 'seconds' && !empty($data['expires_in'])) {
            return now()->addSeconds((int) $data['expires_in']);
        }

        if ($this->expiry_type === 'timestamp' && !empty($data['expires_at'])) {
            return \Carbon\Carbon::parse($data['expires_at']);
        }

        return null;
    }

    public function isTokenExpiredFromResponse(array $response, int $statusCode): bool
    {
        $rules = $this->token_expiry_rules;

        if (!$rules) {
            return false;
        }

        if (!empty($rules['status_code']) && $statusCode == $rules['status_code']) {
            return true;
        }

        if (!empty($rules['error_key']) && !empty($rules['error_value'])) {
            return data_get($response, $rules['error_key']) == $rules['error_value'];
        }

        return false;
    }
}
