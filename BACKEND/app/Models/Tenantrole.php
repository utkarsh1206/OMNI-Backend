<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantRole extends Model
{
    use SoftDeletes;

    protected $table = 'tenant_roles';
    
    protected $fillable = [
        "name",
        "permission",
        "description",
        "status"
    ];
}
