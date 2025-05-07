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

    public function actions()
    {
        return $this->belongsToMany(Action::class, 'role_action', 'role_id', 'action_id')
            ->withPivot('tenant_module_id')
            ->withTimestamps();
    }

    public function tenantModules()
    {
        return $this->belongsToMany(TenantModule::class, 'role_action', 'role_id', 'tenant_module_id')
            ->withPivot('action_id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(Users::class, 'role_user', 'role_id', 'user_id');
    }
}
