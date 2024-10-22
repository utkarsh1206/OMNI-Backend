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

        // Start the query for tenants
        $query = usergroup::query()->where('is_delete', 'No');

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orwhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $usertgroup = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $usertgroup = $query->get();
            return response()->json([
                'data' => $usertgroup,
            ]);
        } else {
            // Paginate the results
            $usertgroup = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $usertgroup->items(),
                'meta' => [
                    'current_page' => $usertgroup->currentPage(),
                    'last_page' => $usertgroup->lastPage(),
                    'per_page' => $usertgroup->perPage(),
                    'total' => $usertgroup->total(),
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
            "description" =>$request->description,
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
            "description" =>$request->description,
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
        $usergroup->is_delete = 'Yes';
        $usergroup->save();
        return response()->json([
            'message' => 'User Group Deleted Successfully',
        ]);
    }

}
