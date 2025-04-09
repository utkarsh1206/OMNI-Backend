<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailDetail extends Model
{
    use HasFactory;

    // Add table name if it doesn't follow the default naming convention
    protected $fillable = [
        'date',
        'user_id',
        'type',
        'priority',
        'status',
        'to',
        'cc',
        'bcc',
        'body',
        'attachment',
        'supportticket_id'
    ];
}
