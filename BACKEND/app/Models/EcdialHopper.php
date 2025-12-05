<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcdialHopper extends Model
{
    protected $table = 'ecdial_hopper';

    protected $primaryKey = 'hopper_id';

    protected $fillable = [
        'lead_id',
        'campaign_id',
        'status',
        'user',
        'list_id',
        'gmt_offset_now',
        'state',
        'alt_dial',
        'priority',
        'source',
    ];
}
