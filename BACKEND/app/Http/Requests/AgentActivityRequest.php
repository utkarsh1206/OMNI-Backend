<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentActivityRequest extends FormRequest
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'users' => 'required|array|min:1',
            'campaign' => 'nullable|string',
            'format' => 'nullable|string|in:json,csv,pdf,graph',
        ];
    }
}
