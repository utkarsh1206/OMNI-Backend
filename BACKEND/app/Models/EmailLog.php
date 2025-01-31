<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'ticket_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function email_template()
    {
        return $this->belongsTo(MailTemplate::class, 'email_template_id');
    }


}
