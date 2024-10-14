<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    use HasFactory;
    protected $table = 'cities';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "country_id",
        "state_id",
        "city_name",
        "city_code",
        "status",
        "is_delete",
        "created_at",
        "updated_at"
    ];}
