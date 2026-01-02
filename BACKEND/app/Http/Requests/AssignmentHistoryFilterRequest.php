<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentHistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id'       => 'nullable|exists:support_tickets,id',
            'assigned_to_id'  => 'nullable|exists:users,id',
            'from_date'       => 'nullable|date',
            'to_date'         => 'nullable|date|after_or_equal:from_date',
        ];
    }
}
