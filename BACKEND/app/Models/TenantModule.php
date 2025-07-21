<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    protected $fillable = [
        'name',
        'display_name'
    ];

    public function actions()
    {
        return $this->belongsToMany(Action::class, 'role_action', 'tenant_module_id', 'action_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
}
