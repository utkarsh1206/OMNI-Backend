<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "country_id",
        "state_id",
        "city_name",
        "city_code",
        "status",
        "created_at",
        "updated_at"
    ];}
