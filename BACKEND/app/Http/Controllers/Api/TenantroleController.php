<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenantrole;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\Validator;


class TenantroleController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');

        // Start the query for tenants
        $query = tenantrole::query()->where('is_delete', 'No');

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orwhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $tenant_role = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $tenant_role = $query->get();
            return response()->json([
                'data' => $tenant_role,
            ]);
        } else {
            // Paginate the results
            $tenant_role = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $tenant_role->items(),
                'meta' => [
                    'current_page' => $tenant_role->currentPage(),
                    'last_page' => $tenant_role->lastPage(),
                    'per_page' => $tenant_role->perPage(),
                    'total' => $tenant_role->total(),
                ],
            ]);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tenant_role',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $tenantrole = tenantrole::create([
            'name' => $request->name,
            "permission" => $request->permission,
            "description" =>$request->description,
            "status" => $request->status      
        ]);
        return response()->json([
            'message' => 'Tenant Role Created Successfully',
            'data' => new TenantsResource($tenantrole)
        ]);
    }
    public function show(Tenantrole $tenantrole)
    {
        return new TenantsResource($tenantrole);
    }
    public function update(Request $request, Tenantrole $tenantrole)
    {
        $tenantrole->update([
            'name' => $request->name,
            "permission" => $request->permission,
            "description" =>$request->description,
            "status" => $request->status      
        ]);
        return response()->json([
            'message' => 'Tenant Role Updated Successfully',
            'data' => new TenantsResource($tenantrole)
        ]);
    }    
    public function destroy(Tenantrole $tenantrole)
    {
        $tenantrole->is_delete = 'Yes';
        $tenantrole->save();
        return response()->json([
            'message' => 'Tenant Role Deleted Successfully',
        ]);
    }
}
