<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcdialStateCallTime extends Model
{
    protected $table = 'ecdial_state_call_times';
    protected $primaryKey = 'state_call_time_id';
    public $timestamps = false;

    protected $fillable = [
        'state_call_time_id',
        'state_call_time_name',
        'state_call_time_comments',
        'state',
        'gmt_offset',
        'active',
    ];
}
