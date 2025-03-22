<?php
    
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Convert status to boolean before validation
        if ($this->has('status')) {
            $this->merge([
                'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question' => 'required|string|unique:chatbots,question',
            'answer' => 'required|string',
            'status' => 'required|boolean',
            'keywords' => 'nullable|array|max:5',
            'keywords.*' => 'string',
        ];
    }
}
