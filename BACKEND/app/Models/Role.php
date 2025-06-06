<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'status'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    public function actions()
    {
        return $this->hasManyThrough(Action::class, Permission::class, 'id', 'id', null, 'action_id');
    }

    public function tenantModules()
    {
        return $this->hasManyThrough(TenantModule::class, Permission::class, 'id', 'id', null, 'module_id');
    }

    public function users()
    {
        return $this->belongsToMany(Users::class, 'role_user', 'role_id', 'user_id');
    }
}
