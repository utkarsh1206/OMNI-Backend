<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcdialCloserLog extends Model
{
    use HasFactory;

    protected $table = 'ecdial_closer_log';

    // Define the primary key as 'closecallid'
    protected $primaryKey = 'closecallid';

    // If 'closecallid' is not an auto-incrementing integer
    public $incrementing = false;

    // If 'closecallid' is not an integer, specify the type
    protected $keyType = 'string';

    public function agentTransitionLogs()
    {
        return $this->hasMany(AgentTransitionLog::class, 'call_uniqueId', 'uniqueid');
    }

    public function campaign()
    {
        return $this->belongsTo(EcdialCampaign::class, 'campaign_id', 'campaign_id');
    }

    public function group()
    {
        return $this->belongsTo(EcdialInboundGroup::class, 'group_id', 'group_id');
    }

    public function status()
    {
        return $this->belongsTo(EcdialStatus::class, 'status', 'status');
    }

    public function userDetail()
    {
        return $this->belongsTo(EcdialUser::class, 'user', 'user');
    }
}

