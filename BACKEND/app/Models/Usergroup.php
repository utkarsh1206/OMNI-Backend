<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usergroup extends Model
{
 protected $table = 'usergroup';

    protected $fillable = [
        //"tenant_id",
        "name",
        "campaign_id",
        "description",
        "status"
    ];
}
