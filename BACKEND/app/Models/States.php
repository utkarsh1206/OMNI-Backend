<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class States extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'states';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "country_id",
        "state_name",
        "state_code",
        "status",
        "created_at",
        "updated_at"
    ];}
