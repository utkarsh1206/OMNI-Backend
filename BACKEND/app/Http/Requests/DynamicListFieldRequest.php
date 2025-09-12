<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
class DynamicListFieldRequest extends FormRequest
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
        $fieldRules = [
            'list_id' => ['required', Rule::exists('ecdial_lists', 'list_id')],
            'field_name' => ['required', 'string', 'max:255'],
            'field_label' => ['required', 'string', 'max:255'],
            'field_type' => [
                'required',
                Rule::in(['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date', 'number'])
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'options' => ['nullable', 'string', 'max:255'],
            'mandatory' => ['required', Rule::in(['Y', 'N'])],
            'calling_authority' => ['required', Rule::in(['Y', 'N'])],
            'show_authority' => ['required', Rule::in(['Y', 'N'])],
            'agent_view' => ['required', Rule::in(['Y', 'N'])],
            'field_order' => ['required', 'integer'],
            'field_help' => ['nullable', 'string'],
            'field_size' => ['nullable', 'integer'],
            'field_max_length' => ['nullable', 'integer'],
            'search_filter' => ['required', Rule::in(['Y', 'N'])],
            'editable' => ['required', Rule::in(['Y', 'N'])],
            'default_value' => ['nullable', 'string', 'max:255'],
            'value_masked' => ['required', Rule::in(['Y', 'N'])],
            'duplicate_check' => ['required', Rule::in(['Y', 'N'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'group_name' => ['nullable', 'string', 'max:255'],
            'send_to_crm' => ['nullable', Rule::in(['Y', 'N'])],
            'allow_field_deletion' => ['nullable', Rule::in(['Y', 'N'])],
            'allow_data_delete' => ['nullable', Rule::in(['Y', 'N'])],
        ];

        return [
            'list_id' => 'required|exists:ecdial_lists,list_id',
            '*.A' => 'array',
            '*.B' => 'array',
            '*.C' => 'array',
            '*.D' => 'array',
            'A.*' => $fieldRules,
            'B.*' => $fieldRules,
            'C.*' => $fieldRules,
            'D.*' => $fieldRules,
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
