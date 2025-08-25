<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    protected $fillable = [
        'list_id',
        'url',
        'method',
        'payload_type',
        'auth_config'
    ];

    protected $casts = [
        'auth_config' => 'array',
    ];

    public function mappings()
    {
        return $this->hasMany(ApiMapping::class, 'endpoint_id');
    }
}
