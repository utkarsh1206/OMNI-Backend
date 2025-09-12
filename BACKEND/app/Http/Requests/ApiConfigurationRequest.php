<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
class ApiConfigurationRequest extends FormRequest
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
        $providerId = $this->route('api_configuration');

        return [
            'provider_name' => [
                'required',
                Rule::unique('providers', 'name')->ignore($providerId),
            ],
            'configurations' => 'required|array',
            'api_config_type_id' => 'required|exists:api_configuration_types,id',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        return failedValidation($validator);
    }
}
