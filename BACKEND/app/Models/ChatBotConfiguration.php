<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBotConfiguration extends Model
{
    protected $fillable = [
        'chat_id',
        'config_key',
        'config_value',
        'description',
    ];
}
