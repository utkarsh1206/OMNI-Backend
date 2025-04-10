<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_extension',
        'campaign_id',
        'call_unique_id',
        'mobile_username',
        'mobile_number',
        'call_date',
        'call_status',
        'actual_call_status',
        'dispose_status',
        'call_direction',
    ];

     // Define the attributes that should be cast to native types (for dates, enums, etc.)
     protected $casts = [
        'call_date' => 'datetime',
        'actual_call_status' => 'string',
        'dispose_status' => 'string',
    ];
    
    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
