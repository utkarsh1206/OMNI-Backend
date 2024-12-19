<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailTemplate extends Model
{
    use HasFactory;

    protected $table = 'mail_template';
    protected $fillable = [
        'title',
        'type',
        'subject',
        'mail_for',
        'body',
        'status',
        'is_delete',
        'is_allow',
        'created_at',
        'updated_at',
    ];
}
