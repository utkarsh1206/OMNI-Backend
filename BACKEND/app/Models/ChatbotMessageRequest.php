<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessageRequest extends Model
{
    protected $fillable = [
        'chat_id',
        'agent_id',
        'customer_id',
        'status',
    ];

    public function attachments()
    {
        return $this->hasMany(ChatbotMessage::class);
    }
}
