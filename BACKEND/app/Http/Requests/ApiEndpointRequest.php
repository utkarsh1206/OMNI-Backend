<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiEndpointRequest extends FormRequest
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
        // For the 'store' method
        $rules = [
            'list_id' => [
                'required',
                'integer',
                Rule::exists('ecdial_lists', 'list_id'),
            ],
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'method' => 'required|in:GET,POST',
            'payload_type' => 'required|in:json,query',
            'auth_config' => 'nullable|array',
            'mappings' => 'required|array',
            'mappings.*.api_key' => 'required|string',
            'mappings.*.column_name' => 'required|string',
        ];

        // For the 'update' method
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules = [
                'list_id' => [
                    'sometimes',
                    'integer',
                    Rule::exists('ecdial_lists', 'list_id'),
                ],
                'name' => 'sometimes|string|max:255',
                'url' => 'sometimes|url',
                'method' => 'sometimes|in:GET,POST',
                'payload_type' => 'sometimes|in:json,query',
                'auth_config' => 'nullable|array',
                'mappings' => 'sometimes|array',
                'mappings.*.api_key' => 'required|string',
                'mappings.*.column_name' => 'required|string',
            ];
        }

        return $rules;
    }
}
