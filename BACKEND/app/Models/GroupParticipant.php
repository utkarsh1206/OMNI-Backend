<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupParticipant extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'can_send_message',
        'last_read_message_id',
        'joined_at',
        'left_at'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
