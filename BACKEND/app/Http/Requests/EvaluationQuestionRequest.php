<?php

namespace App\Http\Requests;

use App\Models\EvaluationQuestionUsageType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluationQuestionRequest extends FormRequest
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
        $id = $this->route('question'); // Get the ID from the route (works for /evaluation-questions/{id})

        return [
            'question' => [
                'required',
                'string',
                Rule::unique('ecdial_evaluation_questions', 'question')->ignore($id),
            ],
            'question_type' => 'required|string|in:TEXT,RADIO,DROPDOWN,MULTI',
            'answers' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $type = $this->input('question_type');

                    if (in_array($type, ['RADIO', 'DROPDOWN', 'MULTI']) && empty($value)) {
                        $fail('The answers field is required when question type is RADIO, DROPDOWN, or MULTI.');
                    }

                    if ($type === 'TEXT' && !empty($value)) {
                        $fail('The answers field must be empty when question type is TEXT.');
                    }
                }
            ],
            'answers.*' => 'string',
            'status' => 'sometimes|string|in:active,inactive',
            'usage_type' => 'required',
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
