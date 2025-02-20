<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occupations extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'occupations';
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
