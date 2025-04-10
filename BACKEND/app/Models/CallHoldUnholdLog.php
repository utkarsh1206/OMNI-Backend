<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallHoldUnholdLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'call_unique_id',
        'mobile_username',
        'mobile_number',
        'call_date',
        'call_status',
        'call_direction',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
