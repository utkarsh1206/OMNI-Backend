<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NeoxCallDispositionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'agent_neox_phone',
        'call_unique_id',
        'mobile_username',
        'mobile_number',
        'disposition_status',
        'disposition_detail',
    ];
}
