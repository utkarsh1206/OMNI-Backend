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
        $roles = Role::with(['tenantModules', 'actions'])->paginate($request->per_page ?? 10);
        return response()->json($roles);
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        // foreach ($request->permissions as $perm) {
        //     $module = TenantModule::firstOrCreate([
        //         'name' => $perm['module']
        //     ], [
        //         'display_name' => $perm['module']
        //     ]);

        //     foreach ($perm['actions'] as $actionName) {
        //         $action = Action::firstOrCreate([
        //             'name' => $actionName
        //         ], [
        //             'display_name' => $actionName
        //         ]);

        //         $role->actions()->attach($action->id, [
        //             'tenant_module_id' => $module->id
        //         ]);
        //     }
        // }

        // foreach ($request->permissions as $perm) {
        //     $module = TenantModule::where('name', $perm['module'])->first();
        //     if (! $module) continue;

        //     foreach ($perm['actions'] as $actionName) {
        //         $action = Action::where('name', $actionName)->first();
        //         if (! $action) continue;

        //         $role->actions()->attach($action->id, [
        //             'tenant_module_id' => $module->id
        //         ]);
        //     }
        // }

        // foreach ($request->permissions as $perm) {
        foreach ($request->get('permissions', []) as $perm) {
            $moduleId = $perm['tenant_module_id'];
            $actionIds = $perm['action_ids'];

            // Optional: validate IDs exist
            $module = TenantModule::find($moduleId);
            if (! $module) continue;

            $validActions = Action::whereIn('id', $actionIds)->pluck('id')->toArray();

            foreach ($validActions as $actionId) {
                $role->actions()->attach($actionId, [
                    'tenant_module_id' => $moduleId
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

        // foreach ($request->permissions as $perm) {
        //     $module = TenantModule::firstOrCreate([
        //         'name' => $perm['module']
        //     ], [
        //         'display_name' => $perm['module']
        //     ]);

        //     foreach ($perm['actions'] as $actionName) {
        //         $action = Action::firstOrCreate([
        //             'name' => $actionName
        //         ], [
        //             'display_name' => $actionName
        //         ]);

        //         $role->actions()->attach($action->id, [
        //             'tenant_module_id' => $module->id
        //         ]);
        //     }
        // }

        // foreach ($request->permissions as $perm) {
        foreach ($request->get('permissions', []) as $perm) {
            $moduleId = $perm['tenant_module_id'];
            $actionIds = $perm['action_ids'];

            // Optional: validate IDs exist
            $module = TenantModule::find($moduleId);
            if (! $module) continue;

            $validActions = Action::whereIn('id', $actionIds)->pluck('id')->toArray();

            foreach ($validActions as $actionId) {
                $role->actions()->attach($actionId, [
                    'tenant_module_id' => $moduleId
                ]);
            }
        }

        return response()->json($role->load(['tenantModules', 'actions']));
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted Successfully'], 200);
    }
}
