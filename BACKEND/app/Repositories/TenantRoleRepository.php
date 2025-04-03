<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\TenantRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;

class TenantRoleRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            $usergroup =   $page === config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.data_fetch'));
        } catch (Exception $ex) {
            Log::error("Error fetching Support Tickets: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
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
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenantrole, trans('message.tenant_role_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Tenant Role Create : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }


    public function update(Request $request, $tenantrole)
    {
        try {
            $tenantrole->update([
                'name' => $request->name,
                "permission" => $request->permission,
                "description" => $request->description,
                "status" => $request->status
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenantrole, trans('message.tenant_role_updated'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Tenant Role Update : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($tenantrole)
    {
        try {
            $tenantrole->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenantrole, trans('message.tenant_role_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Tenant Role Delete : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function menuListPermissions()
    {
        try {
            $final_permission_array = array();
            $permissions = DB::table('omnichannel.menu_permissions')->get();
            if (!empty($permissions)) {
                foreach ($permissions as $permission_value) {
                    $final_permission_array[$permission_value->module_name] = explode(",", $permission_value->roles);
                }
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $final_permission_array, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Tenant Role Menu Permissions : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
