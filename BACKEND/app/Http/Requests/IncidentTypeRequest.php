<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidentTypeRequest extends FormRequest
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
        $id = $this->route('incidentType');
        $rules = [
            'name' => 'required|string|unique:incident_types,name',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:Normal,Low,Medium,High,Critical',
            'sla_id' => 'nullable|exists:slaSettings,id',
        ];

        if ($id) {
            $rules['name'] = 'required|string|unique:incident_types,name,' . $id;
        }

        return $rules;
    }
}
