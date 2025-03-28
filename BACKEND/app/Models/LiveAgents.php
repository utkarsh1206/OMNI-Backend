<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveAgents extends Model
{
    use HasFactory;

    protected $table = 'live_agents';

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
    ];

    protected $casts = [
        'status_last_updated_at' => 'datetime',
        'login_time' => 'datetime',
        'ready_hr' => 'datetime',
        'talk_hr' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(EcdialCampaign::class);
    }

    public function ingroup()
    {
        return $this->belongsTo(EcdialInboundGroup::class);
    }
}
