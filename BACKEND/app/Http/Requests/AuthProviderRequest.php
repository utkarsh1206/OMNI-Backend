<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthProviderRequest extends FormRequest
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
        $isCreate = $this->isMethod('POST');
        $rule = $isCreate ? 'required' : 'sometimes';

        return [
            'name' => [$rule, 'string', 'max:255'],
            'type' => [$rule, 'in:none,api_key,basic,bearer_token,oauth2,hmac,jwt'],

            'token_url'          => 'nullable|string',
            'method'             => 'nullable|in:GET,POST,PUT,DELETE',

            'credentials'        => 'nullable|array',
            'request_config'     => 'nullable|array',
            'response_mapping'   => 'nullable|array',

            'expiry_type'        => 'nullable|in:seconds,timestamp',
            'token_expiry_rules' => 'nullable|array',

            'token_cache_enabled' => 'nullable|boolean',
            'get_on_each_request' => 'nullable|boolean',
        ];
    }
}
