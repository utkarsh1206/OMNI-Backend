<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class GetCustomerMessagesRequest extends FormRequest
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
            'id' => [
                'required',
                'integer',
                'exists:contacts,id'
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Customer ID is required.',
            'id.integer'  => 'Customer ID must be a valid integer.',
            'id.exists'   => 'The selected Customer ID does not exist in contacts.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return failedValidation($validator);
    }
}
