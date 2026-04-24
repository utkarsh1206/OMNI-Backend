<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiConfigRequest extends FormRequest
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
        // return [
        //     'name' => 'required|string|max:255',
        //     'type' => 'nullable|string',

        //     'base_url' => 'required|string',
        //     'endpoint' => 'required|string',

        //     'method' => 'required|in:GET,POST,PUT,DELETE',

        //     'auth_type' => 'required|in:none,api_key,basic',
        //     'auth_provider_id' => 'nullable|exists:auth_providers,id',

        //     'auth_input' => 'nullable|string',
        //     'auth_location' => 'nullable|in:header,query,body',

        //     'headers' => 'nullable|array',
        //     'query_params' => 'nullable|array',
        //     'body' => 'nullable|array',

        //     'timeout' => 'nullable|integer',
        //     'retry_count' => 'nullable|integer',
        // ];

        $isCreate = $this->isMethod('POST');
        $rule = $isCreate ? 'required' : 'sometimes';

        return [
            'name'             => [$rule, 'string', 'max:255'],
            'type'             => 'nullable|string',
            'base_url'         => [$rule, 'string'],
            'endpoint'         => [$rule, 'string'],
            'method'           => [$rule, 'in:GET,POST,PUT,DELETE'],
            'auth_type'        => [$rule, 'in:none,api_key,basic'],
            'auth_provider_id' => 'nullable|exists:auth_providers,id',
            'auth_input'       => 'nullable|string',
            'auth_location'    => 'nullable|in:header,query,body',
            'headers'          => 'nullable|array',
            'query_params'     => 'nullable|array',
            'body'             => 'nullable|array',
            'timeout'          => 'nullable|integer',
            'retry_count'      => 'nullable|integer',
        ];
    }

    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {
        //     if ($this->auth_provider_id && $this->auth_type !== 'none') {
        //         $validator->errors()->add('auth_type', 'Use either auth_provider OR inline auth.');
        //     }
        // });

        $validator->after(function ($validator) {
            $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

            // Shared validation: Don't allow auth_provider AND inline auth
            if ($this->auth_provider_id && $this->auth_type !== 'none') {
                $validator->errors()->add('auth_type', 'Use either auth_provider OR inline auth.');
            }

            // Specific to Update: Ensure input is provided for inline auth
            if ($isUpdate && $this->auth_type && $this->auth_type !== 'none' && !$this->auth_input) {
                $validator->errors()->add('auth_input', 'Auth input required for inline auth.');
            }
        });
    }
}
