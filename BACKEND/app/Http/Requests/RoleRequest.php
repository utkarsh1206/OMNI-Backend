<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function rules()
    {
        $roleId = $this->route('role')?->id;

        return [
            'name'        => 'required|unique:roles,name,' . $roleId,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'status'      => 'boolean',
            'type'=>'required',
            // // 'permissions' => 'required|array|min:1',
            // // 'permissions.*.module'  => 'required|string',
            // // 'permissions.*.actions' => 'required|array|min:1',
            // 'permissions' => 'required|array|min:1',
            // 'permissions.*.tenant_module_id' => 'required|integer|exists:tenant_modules,id',
            // 'permissions.*.action_ids'       => 'required|array|min:1',
            // 'permissions.*.action_ids.*'     => 'required|integer|exists:actions,id',
            'permissions' => 'nullable|array',
            'permissions.*.tenant_module_id' => 'required_with:permissions.*.action_ids|integer|exists:tenant_modules,id',
            'permissions.*.action_ids'       => 'required_with:permissions.*.tenant_module_id|array',
            'permissions.*.action_ids.*'     => 'required|integer|exists:actions,id',
        ];
    }
}
