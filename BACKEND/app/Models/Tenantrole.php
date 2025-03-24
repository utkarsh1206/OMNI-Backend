<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantRole extends Model
{
    use SoftDeletes;
    protected $table = 'tenant_role';
    
    protected $fillable = [
        "name",
        "permission",
        "description",
        "status"
    ];
}
