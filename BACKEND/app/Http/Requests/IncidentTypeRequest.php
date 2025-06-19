<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $id = $this->route('incident_type');

        $rules = [
            // 'name' => 'required|string|unique:incident_types,name',
            'name' => [
                'required',
                'string',
                Rule::unique('incident_types')->where(function ($query) {
                    return $query->where('category_id', $this->input('category_id'));
                })->ignore($id),
            ],
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|exists:priorities,id',
            'sla_id' => 'nullable|exists:sla_settings,id',
        ];

        // if ($id) {
        //     $rules['name'] = 'required|string|unique:incident_types,name,' . $id;
        // }

        return $rules;
    }
}
