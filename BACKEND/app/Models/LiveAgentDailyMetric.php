<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveAgentDailyMetric extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'login_seconds',
        'ready_seconds',
        'talk_seconds'
    ];
}
