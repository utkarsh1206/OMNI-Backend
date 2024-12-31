<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supportticketnotification extends Model
{
    use HasFactory;
    protected $table = 'support_ticket_notification';
    protected $fillable = [
        'type',
        'comment',
        'date',
        'support_ticket_id'
    ];
}
