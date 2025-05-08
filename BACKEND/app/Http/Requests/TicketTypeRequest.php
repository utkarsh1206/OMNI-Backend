<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketTypeRequest extends FormRequest
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
            'type' => 'required|string|max:255|unique:ticket_types,type',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    
        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $ticketTypeId = $this->route('id');
            
            $rules['type'] = 'sometimes|required|string|max:255|unique:ticket_types,type,' . $ticketTypeId;
            $rules['status'] = 'sometimes|required|in:active,inactive';
        }
    
        return $rules;
    }
}
