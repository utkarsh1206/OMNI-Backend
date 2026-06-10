<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiButtonConfigMapping extends Model
{
    protected $fillable = [
        'button_key',
        'api_config_id',
        'is_active',
        'display_name',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function apiConfig(): BelongsTo
    {
        return $this->belongsTo(ApiConfig::class);
    }
}
