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
            'permissions' => 'required|array|min:1',
            'permissions.*.module'  => 'required|string',
            'permissions.*.actions' => 'required|array|min:1',
        ];
    }
}
