<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiMappingRequest extends FormRequest
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
        // For a single insert or an update request
        $rules = [
            'endpoint_id' => [
                'integer',
                Rule::exists('api_endpoints', 'id'),
            ],
            // 'api_key' => 'string',
            // 'column_name' => 'string'
            'api_key' => ['string'], // Changed from 'string' to ['string']
            'column_name' => ['string'] // Changed from 'string' to ['string']
        ];

        // If it's a POST request, determine if it's a bulk insert or a single one
        if ($this->isMethod('post')) {
            // If the first element is an array, it's a bulk insert
            if (is_array($this->input()) && !empty($this->input()) && is_array($this->input()[0])) {
                // Bulk insert rules with wildcard
                return [
                    '*.endpoint_id' => array_merge($rules['endpoint_id'], ['required']),
                    '*.api_key' => array_merge($rules['api_key'], ['required']),
                    '*.column_name' => array_merge($rules['column_name'], ['required'])
                ];
            } else {
                // Single insert rules
                return [
                    'endpoint_id' => array_merge($rules['endpoint_id'], ['required']),
                    'api_key' => array_merge($rules['api_key'], ['required']),
                    'column_name' => array_merge($rules['column_name'], ['required'])
                ];
            }
        }

        // For PUT/PATCH requests (updates), fields are optional ('sometimes')
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'endpoint_id' => array_merge($rules['endpoint_id'], ['sometimes']),
                'api_key' => array_merge($rules['api_key'], ['sometimes']),
                'column_name' => array_merge($rules['column_name'], ['sometimes'])
            ];
        }

        return $rules;
    }
}