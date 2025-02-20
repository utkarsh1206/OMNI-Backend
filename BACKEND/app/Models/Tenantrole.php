<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantRole extends Model
{
    protected $table = 'tenant_role';

    protected $fillable = [
        "name",
        "permission",
        "description",
        "status"
    ];
}
