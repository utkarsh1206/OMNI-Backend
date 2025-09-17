<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class EcdialListFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
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
            'list_id' => 'required|exists:ecdial_lists,list_id',
            'field_name' => 'required|string|max:255',
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,select,multi,checkbox,radio,textarea,date,number',
            'description' => 'nullable|string|max:255',
            'options' => 'nullable|string|max:255',
            'mandatory' => 'required|in:Y,N',
            'calling_authority' => 'required|in:Y,N',
            'show_authority' => 'required|in:Y,N',
            'agent_view' => 'required|in:Y,N',
            'field_order' => 'required|integer',
            'field_help' => 'nullable|string',
            'field_size' => 'nullable|integer',
            'field_max_length' => 'nullable|integer',
            'search_filter' => 'required|in:Y,N',
            'editable' => 'required|in:Y,N',
            'default_value' => 'nullable|string|max:255',
            'value_masked' => 'required|in:Y,N',
            'duplicate_check' => 'required|in:Y,N',
            'status' => 'required|in:active,inactive',
        ];
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
