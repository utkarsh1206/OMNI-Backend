<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlaTarget extends Model
{
    use HasFactory;

    protected $table = 'sla_targets';

    protected $fillable = [
        'sla_id',
        'escalation_level',
        'priority_type',
        'response_time_value',
        'alert_before_response_value',
        'resolve_time_value',
        'alert_before_resolve_value',
    ];
}
