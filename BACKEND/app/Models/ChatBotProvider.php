<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBotProvider extends Model
{
    protected $fillable = [
        'name',
        'status'
    ];
}
