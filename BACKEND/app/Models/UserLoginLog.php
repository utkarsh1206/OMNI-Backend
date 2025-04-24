<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'login_date',
        'login_time',
        'logout_date',
        'logout_time',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
}
