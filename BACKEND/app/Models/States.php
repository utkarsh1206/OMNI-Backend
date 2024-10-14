<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    use HasFactory;
    protected $table = 'states';
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "country_id",
        "state_name",
        "state_code",
        "status",
        "is_delete",
        "created_at",
        "updated_at"
    ];}
