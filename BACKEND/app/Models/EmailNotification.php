<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'ticket_id'];


}
