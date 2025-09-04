<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $fillable = [
        'session_id',
        'tenant_id',
        'customer_id',
        'session_api_token',
        'customer_token',
    ];

    protected $casts = [
        'session_id'        => 'string',
        'tenant_id'         => 'string',
        'customer_id'       => 'integer',
        'session_api_token' => 'string',
        'customer_token'    => 'string',
    ];
}
