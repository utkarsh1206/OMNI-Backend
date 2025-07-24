<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'name' => 'required|unique:roles,name,' . optional($this->tenantrole)->id ?? null,
             'display_name' => 'required|string',
            'description' => 'nullable|string',
            'status'      => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*.tenant_module_id' => 'required_with:permissions.*.action_ids|integer|exists:tenant_modules,id',
            'permissions.*.action_ids'       => 'required_with:permissions.*.tenant_module_id|array',
            'permissions.*.action_ids.*'     => 'required|integer|exists:actions,id',
        ];
    }
}
