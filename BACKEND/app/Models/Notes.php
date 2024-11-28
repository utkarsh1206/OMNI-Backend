<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notes extends Model
{
    use HasFactory;
    protected $table = 'support_ticket_notes';
    protected $fillable = [
        'date',
        'note',
        'supportticket_id'
    ];
}
