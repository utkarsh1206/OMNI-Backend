<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Users;

class UserRoleRepository
{
    public function getAllUsersWithRoles($perPage = 10)
    {
        $users = Users::with('roles')->paginate($perPage);
        return response()->json($users);
    }

    public function updateUserRoles(Users $user, $roleIds)
    {
        $roles = Role::find($roleIds);
        $user->roles()->sync($roles);
        return response()->json($user->load('roles'));
    }
}
