<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipServerConfiguration extends Model
{
    use HasFactory;

    protected $table = 'sip_server_configuration';

    protected $fillable = [
        'sip_server_url',
        'sip_ua_uri_domain',
    ];
}
