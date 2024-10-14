<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupations extends Model
{
    use HasFactory;
    protected $table = 'occupations';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "user_id",
        "title",
        "status",
        "is_delete",
        "created_at",
        "updated_at"
    ];
}
