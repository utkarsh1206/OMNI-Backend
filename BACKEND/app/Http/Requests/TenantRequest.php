<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantRequest extends FormRequest
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
            'username' => 'required|unique:tenants',
            'domain_name' => 'required|unique:tenants',
            'domain_url' => 'required|unique:tenants',
            'email' => 'required|unique:tenants',
            'tenant_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
