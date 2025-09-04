<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'agent_id',
        'customer_id',
        'session_id',
        'sender',
        'text',
        'external_message_time',
    ];

    protected $casts = [
        'external_message_time' => 'datetime', // Casts external_message_time to a Carbon instance
    ];
    
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }

    // public function getUserFirstNameAttribute()
    // {
    //     return $this->user ? $this->user->first_name : null;
    // }

    // public function getUserLastNameAttribute()
    // {
    //     return $this->user ? $this->user->last_name : null;
    // }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    // public function getAttachmentUrlsAttribute()
    // {
    //     return $this->attachments->map(function ($attachment) {
    //         return asset('storage/attachment/' . $attachment->attachments);
    //         // return url('storage'.DIRECTORY_SEPARATOR.'attachment'.DIRECTORY_SEPARATOR.$attachment->attachments); 
    //     })->toArray();
    // }

    // public function scopeBetweenUsers($query, $userId, $receiverId)
    // {
    //     return $query->where(function ($q) use ($userId, $receiverId) {
    //         $q->where('user_id', $userId)
    //             ->where('receiver_id', $receiverId);
    //     })->orWhere(function ($q) use ($userId, $receiverId) {
    //         $q->where('user_id', $receiverId)
    //             ->where('receiver_id', $userId);
    //     });
    // }
}
