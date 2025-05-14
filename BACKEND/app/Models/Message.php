<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $appends = ['user_first_name', 'user_last_name', 'attachment_urls'];

    protected $hidden = ['user', 'attachments'];

    protected $fillable = [
        'user_id',
        'receiver_id',
        'type',
        'text',
        'is_read',
        'room_id',
        'sender',
        'external_message_time',
        'node_type',
        'session_id',
        'first_name',
        'last_name',
    ];

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

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function getAttachmentUrlsAttribute()
    {
        return $this->attachments->map(function ($attachment) {
            return asset('storage/attachment/' . $attachment->attachments);
            // return url('storage'.DIRECTORY_SEPARATOR.'attachment'.DIRECTORY_SEPARATOR.$attachment->attachments); 
        })->toArray();
    }

    public function scopeBetweenUsers($query, $userId, $receiverId)
    {
        return $query->where(function ($q) use ($userId, $receiverId) {
            $q->where('user_id', $userId)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($userId, $receiverId) {
            $q->where('user_id', $receiverId)
                ->where('receiver_id', $userId);
        });
    }
}
