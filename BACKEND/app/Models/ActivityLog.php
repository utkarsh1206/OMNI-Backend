<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module_name',
        'ipaddress',
        'request_params',
        'request_method',
        'response_params'
    ];

    protected $appends = ["user_name"];
    protected $hidden = ["userInfo"];

    public function userInfo()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserNameAttribute()
    {
        return isset($this->userInfo->first_name) ? $this->userInfo->first_name . ' ' . $this->userInfo->last_name : '';
    }
}
