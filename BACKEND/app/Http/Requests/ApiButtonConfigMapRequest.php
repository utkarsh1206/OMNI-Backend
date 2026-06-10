<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiButtonConfigMapRequest extends FormRequest
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
            'button_key' => 'required|string|max:100',
            'mappings' => 'sometimes|array|min:0',
            'mappings.*.api_config_id' => 'required|integer|distinct|exists:api_configs,id',
            'mappings.*.display_name' => 'nullable|string|max:255',
            'mappings.*.sort_order' => 'nullable|integer|min:0',
            'mappings.*.is_active' => 'nullable|boolean',
        ];
    }
}
