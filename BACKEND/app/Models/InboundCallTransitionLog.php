<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundCallTransitionLog extends Model
{
    use HasFactory;

    protected $table = 'inbound_call_transition_log';
}
