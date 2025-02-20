<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usergroup;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\Validator;




class UsergroupController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');
        $name = $request->input('name', '');
        $campaign_id = $request->input('campaign_id', '');
        $status = $request->input('status', '');

        // Start the query for tenants
        $query = usergroup::query();

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orwhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if (!empty($name)) {
            $query->where('name', $name);
        }
        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }
        if (!empty($status)) {
            $query->where('status', $status);
        }
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $usergroup = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $usergroup = $query->get();
            return response()->json([
                'data' => $usergroup,
            ]);
        } else {
            // Paginate the results
            $usergroup = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $usergroup->items(),
                'meta' => [
                    'current_page' => $usergroup->currentPage(),
                    'last_page' => $usergroup->lastPage(),
                    'per_page' => $usergroup->perPage(),
                    'total' => $usergroup->total(),
                ],
            ]);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:usergroup',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $usergroup = usergroup::create([
            'name' => $request->name,
            "campaign_id" => $request->campaign_id,
            "description" => $request->description,
            //"tenant_id"=>1,//Make it dynamic
            "status" => $request->status
        ]);
        return response()->json([
            'message' => 'User Group Created Successfully',
            'data' => new TenantsResource($usergroup)
        ]);
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
        $usergroup->delete();
        return response()->json([
            'message' => trans('message.user_group_delete_msg'),
        ]);
    }
}
