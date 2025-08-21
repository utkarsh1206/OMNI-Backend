<?php
    
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'question' => 'required|string',
            'lang' => 'required|in:english,hindi',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'question is required.',
            'lang.required' => 'Language is required.',
            'lang.in'       => 'Language must be either english or hindi.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ])
        );
    }
}
