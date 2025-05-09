<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\TenantModule;
use App\Models\Action;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;

class RoleRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $roles = Role::with(['tenantModules', 'actions'])->paginate($request->per_page ?? 10);
            return response()->json($roles);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function create(RoleRequest $request)
    {
        try {
            $role = Role::create($request->validated());

            foreach ($request->get('permissions', []) as $perm) {
                $module = TenantModule::find($perm['tenant_module_id']);
                if (!$module) continue;

                $validActions = Action::whereIn('id', $perm['action_ids'])->pluck('id')->toArray();
                foreach ($validActions as $actionId) {
                    $role->actions()->attach($actionId, ['tenant_module_id' => $module->id]);
                }
            }

            // return $this->apiResponse->getResponseStructure(trans('message.success'), $role->load(['tenantModules', 'actions']), trans('message.create_success'));
            return response()->json($role->load(['tenantModules', 'actions']), 201);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($role)
    {
        try {
            // return $this->apiResponse->getResponseStructure(trans('message.success'), $role->load(['tenantModules', 'actions']));
            return response()->json($role->load(['tenantModules', 'actions']));
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(RoleRequest $request, Role $role)
    {
        try {
            $role->update($request->validated());
            $role->actions()->detach();

            foreach ($request->get('permissions', []) as $perm) {
                $module = TenantModule::find($perm['tenant_module_id']);
                if (!$module) continue;

                $validActions = Action::whereIn('id', $perm['action_ids'])->pluck('id')->toArray();
                foreach ($validActions as $actionId) {
                    $role->actions()->attach($actionId, ['tenant_module_id' => $module->id]);
                }
            }

            // return $this->apiResponse->getResponseStructure(trans('message.success'), $role->load(['tenantModules', 'actions']), trans('message.update_success'));
            return response()->json($role->load(['tenantModules', 'actions']));
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete(Role $role)
    {
        try {
            $role->delete();
            // return $this->apiResponse->getResponseStructure(trans('message.success'), null, trans('message.delete_success'));
            return response()->json(['message' => 'Role deleted Successfully'], 200);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
