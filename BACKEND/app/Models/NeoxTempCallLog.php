<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NeoxTempCallLog extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'neox_temp_call_logs';

    // Define the primary key (optional if 'id' is used by default)
    protected $primaryKey = 'id';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'call_unique_id',
        'campaign_id',
        'caller_username',
        'caller_user_extension',
        'user_group',
        'inbound_group_id',
        'called_phone_number',
        'type',
        'date',
        'time',
        'call_initiated_status',
        'call_connected_status',
        'call_hangup_status',
        'call_type',
        'json_data',
    ];

    // Define the types of attributes
    protected $casts = [
        'date' => 'date',
        'time' => 'time',
    ];
}
