<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroup extends Model
{
    use SoftDeletes;

    protected $table = 'user_groups';
   
    protected $fillable = [
        //"tenant_id",
        "name",
        "department_id",
        "description",
        "status"
    ];
}
