<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chatbot extends Model
{
    use HasFactory;

    protected $fillable = ['question', 'answer', 'status', 'keywords'];

    protected $casts = [
        'status' => 'boolean',
        'keywords' => 'array',
    ];
}
