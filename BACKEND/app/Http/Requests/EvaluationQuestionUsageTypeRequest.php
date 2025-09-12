<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class EvaluationQuestionUsageTypeRequest extends FormRequest
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
        $rules = [
            'usage_type' => 'required|string|unique:evaluation_question_usage_types,usage_type',
            'status' => 'required|in:active,inactive',
        ];

        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $usageTypeId = $this->route('question_usage_type')->id;
            $rules['usage_type'] = 'sometimes|required|string|max:255|unique:evaluation_question_usage_types,usage_type,' . $usageTypeId;
            $rules['status'] = 'sometimes|required|in:active,inactive';
        }

        return $rules;
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
