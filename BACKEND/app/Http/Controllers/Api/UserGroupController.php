<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserGroupRequest;
use Illuminate\Http\Request;
use App\Models\Usergroup;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\Validator;
use App\Repositories\UserGroupRepository;

class UserGroupController extends Controller
{
    private $userGroupRepository;

    public function __construct(UserGroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    public function index(Request $request)
    {
        return $this->userGroupRepository->list($request);
    }
    public function store(UserGroupRequest $request)
    {
        return $this->userGroupRepository->create($request);
    }
    public function show(Usergroup $usergroup)
    {
        return new TenantsResource($usergroup);
    }
    public function update(Request $request, Usergroup $usergroup)
    {
        return $this->userGroupRepository->update($request, $usergroup);
    }
    
    public function destroy(Usergroup $usergroup)
    {
        return $this->userGroupRepository->delete($usergroup);
    }
}
