<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentTransitionLog extends Model
{
    use HasFactory;

    protected $table = 'agent_transition_log';
    public $timestamps = false;

    public function callLog()
    {
        return $this->belongsTo(EcdialCloserLog::class, 'call_uniqueId', 'uniqueid');
    }
}
