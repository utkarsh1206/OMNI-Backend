<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\TenantRole;
use Illuminate\Support\Facades\DB;
use App\Http\Response\CustomApiResponse;
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

            // Advanced filters for TenantRole
            $filters = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => $request->input('status'),
            ];

            $query = TenantRole::query();

            // ✅ LIVE SEARCH — searches across multiple text fields
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // ✅ ADVANCED FILTERS — applies only if specific filter field is given
            if (!empty($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (!empty($filters['description'])) {
                $query->where('description', 'like', "%{$filters['description']}%");
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // ✅ PAGINATION
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $tenantRoles = $page == config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $tenantRoles,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error during Tenant Role List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
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
                    $section = $permission_value->section;
                    $module = $permission_value->module_name;
                    $roles = explode(",", $permission_value->roles);

                    $final_permission_array[$section][$module] = $roles;
                }
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $final_permission_array, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Tenant Role Menu Permissions : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
