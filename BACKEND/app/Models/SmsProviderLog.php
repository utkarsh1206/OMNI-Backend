<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsProviderLog extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'message_id',
        'status',
        'error_message',
    ];
}
