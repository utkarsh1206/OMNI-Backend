<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentRuleRequest extends FormRequest
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
            'name'             => 'required|string|max:255',
            'department_id'    => 'nullable|exists:departments,id',
            'incident_type_id' => 'nullable|exists:incident_types,id',
            'priority_id'      => 'nullable|exists:priorities,id',
            'sla_id'           => 'nullable|exists:sla_settings,id',
            'method'           => 'required|in:round_robin,load_balance,random,priority',
            'parameters'       => 'nullable|array',
            'enabled'          => 'boolean',
            'priority_order'   => 'integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'method.in' => 'Assignment method must be one of: round_robin, load_balance, random, priority.',
        ];
    }
}
