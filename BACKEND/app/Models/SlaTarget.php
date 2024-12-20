<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlaTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'sla_id',
        'priority_type',
        'response_time_value',
        'response_time_unit',
        'alert_before_response_value',
        'alert_before_response_unit',
        'resolve_time_value',
        'resolve_time_unit',
        'alert_before_resolve_value',
        'alert_before_resolve_unit',
    ];
}
