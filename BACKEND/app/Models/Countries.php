<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    use HasFactory;
    protected $table = 'countries';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "country_name",
        "country_code",
        "iso_code",
        "status",
        "is_delete",
        "sort_by",
        "created_at",
        "updated_at"
    ];
}
