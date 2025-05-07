<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Users;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $users = Users::with('roles')->paginate($request->per_page ?? 10);
        return response()->json($users);
    }

    public function update(Request $request, Users $user)
    {
        $roles = Role::find($request->role_ids);
        $user->roles()->sync($roles);
        return response()->json($user->load('roles'));
    }
}
