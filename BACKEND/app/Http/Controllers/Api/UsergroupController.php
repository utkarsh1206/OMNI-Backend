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
       $this->userGroupRepository->list($request);
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
        $usergroup->update([
            'name' => $request->name,
            "campaign_id" => $request->campaign_id,
            "description" => $request->description,
            //"tenant_id"=>1,//Make it dynamic
            "status" => $request->status
        ]);
        return response()->json([
            'message' => 'User Group Updated Successfully',
            'data' => new TenantsResource($usergroup)
        ]);
    }
    public function destroy(Usergroup $usergroup)
    {
       return $this->userGroupRepository->delete($usergroup);
    }
}
