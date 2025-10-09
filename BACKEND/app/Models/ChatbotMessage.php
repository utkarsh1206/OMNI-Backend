<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'chatbot_message_request_id',
        'session_id',
        'sender',
        'text',
        'external_message_time',
    ];

    protected $casts = [
        'external_message_time' => 'datetime', // Casts external_message_time to a Carbon instance
    ];

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function request()
    {
        return $this->belongsTo(ChatbotMessageRequest::class, 'chatbot_message_request_id');
    }
}
