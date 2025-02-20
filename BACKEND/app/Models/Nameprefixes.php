<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Nameprefixes extends Model
{
    use HasFactory , SoftDeletes;
    protected $table = 'name_prefixes';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "title",
        "status",
        "created_at",
        "updated_at"
    ];}
