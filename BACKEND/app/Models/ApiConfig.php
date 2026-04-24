<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiConfig extends Model
{
    protected $fillable = [
        'name',
        'type',
        'base_url',
        'endpoint',
        'method',
        'auth_type',
        'auth_config',
        'auth_provider_id',
        'headers',
        'query_params',
        'body',
        'timeout',
        'retry_count',
        'response_mapping',
        'status',
    ];

    protected $casts = [
        'auth_config' => 'array',
        'headers' => 'array',
        'query_params' => 'array',
        'body' => 'array',
        'response_mapping' => 'array',
        'status' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function authProvider(): BelongsTo
    {
        return $this->belongsTo(AuthProvider::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFullUrl(): string
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($this->endpoint, '/');
    }

    public function usesAuthProvider(): bool
    {
        return !is_null($this->auth_provider_id);
    }

    public function usesInlineAuth(): bool
    {
        return $this->auth_type !== 'none' && !$this->usesAuthProvider();
    }

    /*
    |--------------------------------------------------------------------------
    | Inline Auth Helpers
    |--------------------------------------------------------------------------
    */

    public function getInlineAuthData(): array
    {
        return $this->auth_config ?? [];
    }

    public function getInlineAuthParams(): array
    {
        return $this->auth_config['params'] ?? [];
    }

    public function getInlineAuthLocation(): ?string
    {
        return $this->auth_config['location'] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Request Builders
    |--------------------------------------------------------------------------
    */

    public function buildHeaders(): array
    {
        return $this->headers ?? [];
    }

    public function buildQuery(): array
    {
        return $this->query_params ?? [];
    }

    public function buildBody(): array
    {
        return $this->body ?? [];
    }
}
