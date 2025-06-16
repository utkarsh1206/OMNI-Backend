<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NeoxCallLog extends Model
{
    use HasFactory;

    // Define the table name

    // Define the primary key (optional if 'id' is used by default)
    protected $primaryKey = 'id';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'call_unique_id',
        'campaign_id',
        'campaign_mst_id',
        'inbound_group_id',
        'user_group',
        'call_date',
        'caller_username',
        'caller_user_extension',
        'called_phone_number',
        'call_total_duration',
        'call_talk_duration',
        'call_status',
        'call_dispo_status',
        'call_term_reason',
        'cust_call_time',
        'aband_call_time',
        'comments',
        'call_did_pattern',
        'call_recording_url',
        'type',
        'call_type',
        'json_data',
    ];

    // Define the types of attributes
    protected $casts = [
        'call_date' => 'datetime',
        'cust_call_time' => 'datetime',
        'aband_call_time' => 'datetime',
    ];

    public function dispositionDetail()
    {
        return $this->belongsTo(NeoxCallDispositionDetail::class, 'call_unique_id', 'call_unique_id')
            ->select('call_unique_id', 'disposition_detail');
    }
}
