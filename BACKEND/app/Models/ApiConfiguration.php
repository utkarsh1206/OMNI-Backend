<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiConfiguration extends Model
{
    protected $fillable = [
        'provider_id',
        'config_key',
        'config_value',
        'description',
        'api_config_type_id',
    ];


    
}
