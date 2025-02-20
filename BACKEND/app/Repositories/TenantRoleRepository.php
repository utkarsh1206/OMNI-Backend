<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\TenantRole;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\DB;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class TenantRoleRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');

        // Start the query for tenants
        $query = TenantRole::query();

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
            $tenantRole = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenantRole, trans('message.data_fetch'));
           
        }
    }

    public function create(Request $request)
    {
        try {
        $tenantrole = TenantRole::create([
            'name' => $request->name,
            "permission" => $request->permission,
            "description" => $request->description,
            "status" => $request->status
        ]);
        return response()->json([
            'message' => 'Tenant Role Created Successfully',
            'data' => new TenantsResource($tenantrole)
        ]);
        } catch (QueryException $ex) {
            Log::error('Error during authentication: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.invalid_credentials')],  ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }


    public function update(Request $request, $tenantrole)
    {
        $tenantrole = TenantRole::create([
            'name' => $request->name,
            "permission" => $request->permission,
            "description" => $request->description,
            "status" => $request->status
        ]);
        return response()->json([
            'message' => 'Tenant Role Created Successfully',
            'data' => new TenantsResource($tenantrole)
        ]);
    }

    public function delete($tenantrole)
    {
        $tenantrole->delete();
        return response()->json([
            'message' => 'Tenant Role Deleted Successfully',
        ]);
    }

    public function menuListPermissions()
    {
        $final_permission_array = array();
        $permissions = DB::table('omnichannel.menu_permissions')->get();
        if (!empty($permissions)) {
            foreach ($permissions as $permission_key => $permission_value) {
                $final_permission_array[$permission_value->module_name] = explode(",", $permission_value->roles);
            }
        }

        return  response()->json([
            'data' => new TenantsResource($final_permission_array),
        ]);
    }
}
