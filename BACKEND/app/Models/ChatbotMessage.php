<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'agent_id',
        'customer_id',
        'session_id',
        'sender',
        'text',
        'status',
        'external_message_time',
    ];

    protected $casts = [
        'external_message_time' => 'datetime', // Casts external_message_time to a Carbon instance
    ];

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

}
