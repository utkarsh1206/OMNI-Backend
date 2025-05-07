<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $appends = ['attachment_url'];

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
}
