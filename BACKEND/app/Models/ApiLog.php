<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $fillable = [
        'api_config_id',
        'request',
        'response',
        'status_code',
        'success',
        'error_message',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
        'success' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function apiConfig(): BelongsTo
    {
        return $this->belongsTo(ApiConfig::class);
    }
}
