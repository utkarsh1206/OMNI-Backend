<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\TenantModule;
use App\Models\Action;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with(['tenantModules', 'actions'])->paginate($request->per_page ?? 15);
        return response()->json($roles);
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        foreach ($request->permissions as $perm) {
            $module = TenantModule::firstOrCreate([
                'name' => $perm['module']
            ], [
                'display_name' => $perm['module']
            ]);

            foreach ($perm['actions'] as $actionName) {
                $action = Action::firstOrCreate([
                    'name' => $actionName
                ], [
                    'display_name' => $actionName
                ]);

                $role->actions()->attach($action->id, [
                    'tenant_module_id' => $module->id
                ]);
            }
        }

        return response()->json($role->load(['tenantModules', 'actions']), 201);
    }

    public function show(Role $role)
    {
        return response()->json($role->load(['tenantModules', 'actions']));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        $role->actions()->detach();

        foreach ($request->permissions as $perm) {
            $module = TenantModule::firstOrCreate([
                'name' => $perm['module']
            ], [
                'display_name' => $perm['module']
            ]);

            foreach ($perm['actions'] as $actionName) {
                $action = Action::firstOrCreate([
                    'name' => $actionName
                ], [
                    'display_name' => $actionName
                ]);

                $role->actions()->attach($action->id, [
                    'tenant_module_id' => $module->id
                ]);
            }
        }

        return response()->json($role->load(['tenantModules', 'actions']));
    }

    public function destroy(Role $role)
    {
        generateLogs('info', 'inside delete function');
        $role->delete();
        return response()->json('Role deleted Successfuly', 204);
    }
}
