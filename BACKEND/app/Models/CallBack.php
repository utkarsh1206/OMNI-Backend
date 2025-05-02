<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallBack extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'list_id',
        'campaign_id',
        'user_id',
        'call_back_time',
        'recipient',
        'comments',
        'user_group',
        'lead_status',
        'last_modified_user',
    ];

    protected $casts = [
        'call_back_time' => 'datetime',
    ];
}
