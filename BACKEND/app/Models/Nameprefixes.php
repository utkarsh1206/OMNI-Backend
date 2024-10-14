<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nameprefixes extends Model
{
    use HasFactory;
    protected $table = 'name_prefixes';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "title",
        "status",
        "is_delete",
        "created_at",
        "updated_at"
    ];}
