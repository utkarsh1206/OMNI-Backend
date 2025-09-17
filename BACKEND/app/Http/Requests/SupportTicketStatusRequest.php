<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class SupportTicketStatusRequest extends FormRequest
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
            'name' => 'required|string|unique:support_ticket_statuses,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];

        if ($this->method() === 'PUT') {
            $id = $this->route('ticket_status')?->id ?? null;
            $rules['name'] = 'sometimes|required|string|unique:support_ticket_statuses,name,' . $id;
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
