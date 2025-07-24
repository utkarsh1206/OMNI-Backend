<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveAgents extends Model
{
    use HasFactory;

    protected $table = 'live_agents';

    public $bypassObserver = false;

    protected $appends = [
        'status_last_updated_elapsed',
        'login_duration_elapsed',
        'login_count_today',
        'pause_duration_elapsed',
        'ready_duration_elapsed',
    ];

    protected $fillable = [
        'user_id',
        'extension',
        'status',
        'customer',
        'campaign_id',
        'ingroup_id',
        'status_last_updated_at',
        'total_calls',
        'login_time',
        'ready_hr',
        'talk_hr',
        'talk_avg',
        'ingroup',
        'lagged_duration',
        'rank',
        'pause_seconds',
        'ready_seconds',
    ];

    protected $casts = [
        'status_last_updated_at' => 'datetime',
        'login_time' => 'datetime',
        'ready_hr' => 'datetime',
        'talk_hr' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function campaign()
    {
        return $this->belongsTo(EcdialCampaign::class);
    }

    public function ingroup()
    {
        return $this->belongsTo(EcdialInboundGroup::class);
    }

    public function getStatusLastUpdatedElapsedAttribute()
    {
        if (!$this->status_last_updated_at) return null;
        // $diffInSeconds = now()->diffInSeconds($this->status_last_updated_at);
        $elapsedSeconds = Carbon::parse($this->status_last_updated_at)->diffInSeconds(now(), false);
        // $minutes = floor($diffInSeconds / 60);
        // $seconds = $diffInSeconds % 60;

        if ($elapsedSeconds < 0) {
            return '00:00:00';
        }

        $hours = floor($elapsedSeconds / 3600);
        $minutes = floor(($elapsedSeconds % 3600) / 60);
        $seconds = $elapsedSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getLoginDurationElapsedAttribute()
    {
        if (!$this->login_time) return null;

        $elapsedSeconds = Carbon::parse($this->login_time)->diffInSeconds(now(), false);

        if ($elapsedSeconds < 0) {
            return '00:00:00';
        }

        $hours = floor($elapsedSeconds / 3600);
        $minutes = floor(($elapsedSeconds % 3600) / 60);
        $seconds = $elapsedSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getLoginCountTodayAttribute()
    {
        if (!$this->user_id) {
            return 0;
        }
        try {
            $count = UserLoginLog::where('user_id', $this->user_id)
                ->whereDate('login_date', Carbon::today())
                // ->whereNotNull('logout_time')
                ->count();
            return $count;
        } catch (\Throwable $th) {
            generateLogs("error", "Unable to get the login count today"); // . $th->getMessage()
        }
    }

    public function getPauseDurationElapsedAttribute()
    {
        $baseSeconds = $this->pause_seconds ?? 0;

        if (strtolower($this->status) === 'pause' && $this->status_last_updated_at) {
            // $elapsed = now()->diffInSeconds($this->status_last_updated_at);
            $elapsed = $this->status_last_updated_at->diffInSeconds(now());
            $baseSeconds += max(0, $elapsed); // avoid negative values
        }

        return gmdate('H:i:s', $baseSeconds);
    }

    public function getReadyDurationElapsedAttribute()
    {
        $baseSeconds = $this->ready_seconds ?? 0;

        if (strtolower($this->status) === 'resume' && $this->status_last_updated_at) {
            // $elapsed = now()->diffInSeconds($this->status_last_updated_at);
            $elapsed = $this->status_last_updated_at->diffInSeconds(now());
            $baseSeconds += max(0, $elapsed);
        }

        return gmdate('H:i:s', $baseSeconds);
    }
}
