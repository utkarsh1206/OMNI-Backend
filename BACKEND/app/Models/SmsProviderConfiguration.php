<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsProviderConfiguration extends Model
{
    protected $fillable = [
        'provider_id',
        'config_key',
        'config_value',
        'description',
    ];
}
