<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
class ChatbotAskQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'session_id' => 'required',
            'question' => 'required|string',
            'lang' => 'required|in:en,hi',
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => 'session id is required.',
            'question.required' => 'question is required.',
            'lang.required' => 'Language is required.',
            'lang.in'       => 'Language must be either en or hindi.',
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
