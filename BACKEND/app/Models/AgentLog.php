<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLog extends Model
{
    use HasFactory;

    protected $table = 'agent_log';

    protected $fillable = [
        'user_log_id',
        'user',
        'extension',
        'campaign_id',
        'user_group',
        'event_date',
        'event_stop_epoch',
        'event_start_epoch',
    ];

    public $timestamps = false;

    public function getUser()
    {
        return $this->belongsTo(EcdialUser::class, 'user', 'user');
    }

    public function getCampaign()
    {
        return $this->belongsTo(EcdialCampaign::class, 'campaign_id', 'campaign_id');
    }

    public function getUserGroup()
    {
        return $this->belongsTo(EcdialUserGroup::class, 'user_group', 'user_group');
    }
}
