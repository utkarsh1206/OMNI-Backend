<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    // protected $appends = ['attachment_url'];
    protected $appends = ['attachment_url', 'user_first_name', 'user_last_name'];

    protected $hidden = ['user'];

    protected $fillable = [
        'user_id',
        'receiver_id',
        'type',
        'text',
        'attachment',
        'is_read',
        'room_id',
        'sender',
        'external_message_time',
        'node_type',
        'session_id',
        'first_name',
        'last_name',
    ];

    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) {
            return null;
        }

        if (filter_var($this->attachment, FILTER_VALIDATE_URL)) {
            return $this->attachment;
        }
        return url($this->attachment);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserFirstNameAttribute()
    {
        return $this->user ? $this->user->first_name : null;
    }

    public function getUserLastNameAttribute()
    {
        return $this->user ? $this->user->last_name : null;
    }
}
