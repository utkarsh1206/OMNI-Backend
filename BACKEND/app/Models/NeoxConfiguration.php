<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NeoxConfiguration extends Model
{
    use HasFactory;

    protected $table = 'neox_configuration';

    protected $fillable = [
        'neox_token',
        'neox_local_ip',
        'sip_server_url',
        'sip_ua_uri_domain',
    ];
}
