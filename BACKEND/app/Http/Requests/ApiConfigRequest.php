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
        $isCreate = $this->isMethod('POST');
        $rule = $isCreate ? 'required' : 'sometimes';
     
        return [
            'name'             => [$rule, 'string', 'max:255'],
            'type'             => 'nullable|string',
            'base_url'         => [$rule, 'string'],
            'endpoint'         => [$rule, 'string'],
            'method'           => [$rule, 'in:GET,POST,PUT,DELETE'],
            'auth_type'        => [$rule, 'in:none,api_key,basic,external'],
            'auth_provider_id' => 'nullable|exists:auth_providers,id',
            'auth_input'       => 'nullable|string',
            'auth_location'    => 'nullable|in:header,query,body',
            'headers'          => 'nullable|array',
            'query_params'     => 'nullable|array',
            'body'             => 'nullable|array',
            'timeout'          => 'nullable|integer',
            'retry_count'      => 'nullable|integer',
            'response_mapping' => 'nullable|array',
            'status'           => 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
            $apiConfig = $this->route('apiConfig');

            $incomingAuthType = $this->input('auth_type');
            $incomingProviderId = $this->input('auth_provider_id');

            $effectiveAuthType = $incomingAuthType ?? ($isUpdate ? $apiConfig?->auth_type : null);
            $effectiveProviderId = $incomingProviderId ?? ($isUpdate ? $apiConfig?->auth_provider_id : null);

            // external mode requires provider selection.
            if ($effectiveAuthType === 'external' && empty($effectiveProviderId)) {
                $validator->errors()->add('auth_provider_id', 'Auth provider is required when auth_type is external.');
            }

            // Provider can only be used in external mode.
            if (!empty($incomingProviderId) && $effectiveAuthType !== 'external') {
                $validator->errors()->add('auth_provider_id', 'Auth provider can only be used when auth_type is external.');
            }

            // Inline payload is not allowed for external mode.
            if (
                $incomingAuthType === 'external'
                && ($this->filled('auth_input') || $this->filled('auth_location'))
            ) {
                $validator->errors()->add('auth_input', 'Inline auth fields are not allowed when auth_type is external.');
            }

            // none mode should not receive a provider in the same payload.
            if ($incomingAuthType === 'none' && !empty($incomingProviderId)) {
                $validator->errors()->add('auth_provider_id', 'Auth provider is not allowed when auth_type is none.');
            }

            // Inline auth modes cannot include provider.
            if (
                in_array($effectiveAuthType, ['api_key', 'basic'], true)
                && !empty($incomingProviderId)
            ) {
                $validator->errors()->add('auth_provider_id', 'Use either inline auth or external auth provider.');
            }

            // Preserve existing update behavior for inline auth payloads.
            if (
                $isUpdate
                && in_array((string) $incomingAuthType, ['api_key', 'basic'], true)
                && !$this->filled('auth_input')
            ) {
                $validator->errors()->add('auth_input', 'Auth input required for inline auth.');
            }
        });
    }
}
