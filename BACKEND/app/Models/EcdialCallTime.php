<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcdialCallTime extends Model
{
    protected $primaryKey = 'call_time_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'call_time_id',
        'call_time_name',
        'call_time_comments',

        'ct_default_start',
        'ct_default_stop',

        'ct_sunday_start',
        'ct_sunday_stop',
        'ct_monday_start',
        'ct_monday_stop',
        'ct_tuesday_start',
        'ct_tuesday_stop',
        'ct_wednesday_start',
        'ct_wednesday_stop',
        'ct_thursday_start',
        'ct_thursday_stop',
        'ct_friday_start',
        'ct_friday_stop',
        'ct_saturday_start',
        'ct_saturday_stop',

        'ct_state_call_times',

        'default_afterhours_filename_override',
        'sunday_afterhours_filename_override',
        'monday_afterhours_filename_override',
        'tuesday_afterhours_filename_override',
        'wednesday_afterhours_filename_override',
        'thursday_afterhours_filename_override',
        'friday_afterhours_filename_override',
        'saturday_afterhours_filename_override',
    ];

    protected $casts = [
        'ct_default_start' => 'integer',
        'ct_default_stop' => 'integer',
        'ct_sunday_start' => 'integer',
        'ct_sunday_stop' => 'integer',
        'ct_monday_start' => 'integer',
        'ct_monday_stop' => 'integer',
        'ct_tuesday_start' => 'integer',
        'ct_tuesday_stop' => 'integer',
        'ct_wednesday_start' => 'integer',
        'ct_wednesday_stop' => 'integer',
        'ct_thursday_start' => 'integer',
        'ct_thursday_stop' => 'integer',
        'ct_friday_start' => 'integer',
        'ct_friday_stop' => 'integer',
        'ct_saturday_start' => 'integer',
        'ct_saturday_stop' => 'integer',
    ];
}
