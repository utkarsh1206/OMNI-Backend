<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCdrLead extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'call_unique_id',
        'list_id',
        'lead_id',
        'lead_json',
    ];

    protected $casts = [
        'lead_json' => 'array', // Casts the JSON column to a PHP array
    ];
}
