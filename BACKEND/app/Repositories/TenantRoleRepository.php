<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\TenantRole;
use Illuminate\Support\Facades\DB;
use App\Http\Response\CustomApiResponse;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
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

            $query = Role::query();

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
        // try {
        // $tenantrole = TenantRole::create([
        //     'name' => $request->name,
        //     "permission" => $request->permission,
        //     "description" => $request->description,
        //     "status" => $request->status
        // ]);
           $role = Role::create($request->validated());
              $roleId = $role->id;

            $data = $request->all();
            DB::transaction(function () use ($data, $roleId) {
                foreach ($data['permissions'] as $permSet) {
                    $moduleId = $permSet['tenant_module_id'];
                    foreach ($permSet['action_ids'] as $actionId) {
                        $permission = Permission::firstOrCreate([
                            'module_id' => $moduleId,
                            'action_id' => $actionId,
                        ]);
                        RolePermission::firstOrCreate([
                            'role_id' => $roleId,
                            'permission_id' => $permission->id,
                        ]);
                    }
                }
            });
            return response()->json($role->load(['tenantModules', 'actions']), 201);
        // } catch (Exception $ex) {
        //     generateLogs('error', 'Error during Tenant Role Create : ', $ex->getMessage());
        //     return $this->apiResponse->handleAndResponseException($ex);
        // }
    }


    public function update(Request $request, Role $role)
    {
        try {
            // $tenantrole->update([
            //     'name' => $request->name,
            //     "permission" => $request->permission,
            //     "description" => $request->description,
            //     "status" => $request->status
            // ]);
             $role->update($request->validated());
            //$role->actions()->detach();
            $roleId = $role->id;
            $data = $request->all();
            DB::transaction(function () use ($data, $roleId) {
                $newPermissionIds = [];
                foreach ($data['permissions'] as $permSet) {
                    $moduleId = $permSet['tenant_module_id'];
                    foreach ($permSet['action_ids'] as $actionId) {
                        $permission = Permission::firstOrCreate([
                            'module_id' => $moduleId,
                            'action_id' => $actionId,
                        ]);

                        $newPermissionIds[] = $permission->id;
                        RolePermission::firstOrCreate([
                            'role_id' => $roleId,
                            'permission_id' => $permission->id,
                        ]);
                    }
                }
                RolePermission::where('role_id', $roleId)
                    ->whereNotIn('permission_id', $newPermissionIds)
                    ->delete();
                Permission::whereNotIn('id', RolePermission::pluck('permission_id'))
                    ->delete();
            });
             return response()->json($role->load(['permissions', 'tenantModules']));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Tenant Role Update : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete(Role $role)
    {
        try {
            $role->delete();
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
