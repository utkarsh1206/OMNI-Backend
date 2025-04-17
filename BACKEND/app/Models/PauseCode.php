<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PauseCode extends Model
{
    use HasFactory;
    
    protected $fillable = ['code', 'name', 'description'];
}