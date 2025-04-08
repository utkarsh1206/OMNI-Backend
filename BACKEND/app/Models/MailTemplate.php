<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mail_template';

    protected $fillable = [
        'title',
        'type',
        'subject',
        'mail_for',
        'body',
        'status',
        'is_allow',
        'created_at',
        'updated_at',
    ];
}
