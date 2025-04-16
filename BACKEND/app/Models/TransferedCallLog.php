<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferedCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'call_unique_id',
        'mobile_username',
        'mobile_number',
        'transfer_from',
        'transfer_to',
        'transfer_to_type',
        'call_date',
        'call_direction',
        'transfer_type',
        'call_route_data'
    ];
}
