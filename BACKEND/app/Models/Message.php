<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $appends = ['attachment_url'];
    
    protected $fillable = [
        'user_id',
        'text',
        'attachment',
        'is_read',
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
