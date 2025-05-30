<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
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
            'name' => 'required|string|unique:statuses,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];

        if ($this->method() === 'PUT') {
            $id = $this->route('ticket_status')?->id ?? null;
            $rules['name'] = 'sometimes|required|string|unique:statuses,name,' . $id;
            $rules['status'] = 'sometimes|required|in:active,inactive';
        }

        return $rules;
    }
}
