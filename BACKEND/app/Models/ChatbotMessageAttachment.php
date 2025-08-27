<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessageAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'attachments',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
