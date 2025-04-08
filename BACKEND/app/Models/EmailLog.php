<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'email_template_id', 'from_email', 'module_name', 'entity_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function emailTemplate()
    {
        return $this->belongsTo(MailTemplate::class, 'email_template_id');
    }
}
