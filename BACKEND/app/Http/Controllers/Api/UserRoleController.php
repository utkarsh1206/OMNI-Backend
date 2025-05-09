<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRoleRepository;
use App\Models\Users;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    protected $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository)
    {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function index(Request $request)
    {
        return $this->userRoleRepository->getAllUsersWithRoles($request->per_page ?? 10);
    }

    public function update(Request $request, Users $user)
    {
        return $this->userRoleRepository->updateUserRoles($user, $request->user_role_id);
    }
}
