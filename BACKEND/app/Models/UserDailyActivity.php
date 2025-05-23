<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDailyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'call_unique_id',
        'activity',
        'pause_code',
        'pause_duration',
        'resume_duration',
        'wait_duration',
        'call_duration',
        'talk_duration',
        'dispo_duration',
        'total_login_duration',
        'dispo_code',
    ];

    public function pauseCode()
    {
        return $this->belongsTo(PauseCode::class, 'pause_code', 'id');
    }
}
