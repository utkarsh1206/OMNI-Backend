<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occupation extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $connection = 'mysql';

    protected $fillable = [
        "id",
        "user_id",
        "title",
        "status",
        "created_at",
        "updated_at"
    ];
}
