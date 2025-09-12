<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class RequestOtpRequest extends FormRequest
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
            'email' => [
                'sometimes',
                'required_without:mobile',
                'prohibited_unless:mobile,null',
                'email',
            ],
            'mobile' => [
                'sometimes',
                'required_without:email',
                'prohibited_unless:email,null',
                'numeric',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email is required if mobile is not provided.',
            'email.prohibited_unless' => 'Email should not be present when mobile is provided.',
            'email.email' => 'Please provide a valid email address.',
            'mobile.required_without' => 'Mobile is required if email is not provided.',
            'mobile.prohibited_unless' => 'Mobile should not be present when email is provided.',
            'mobile.numeric' => 'Mobile must contain only numbers.',
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
