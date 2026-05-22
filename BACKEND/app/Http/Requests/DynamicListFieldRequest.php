<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class DynamicListFieldRequest extends FormRequest
{
    private const FORM_COLUMNS = ['A', 'B', 'C', 'D'];

    private const FIELD_TYPES = ['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date', 'number'];

    private const YES_NO = ['Y', 'N'];

    private const DEPENDENCY_PARAM_TYPES = ['query', 'param', 'body', 'header', 'path'];

    private const RESPONSE_TYPES = ['array_values', 'array_objects', 'single_value'];

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
            'field_name' => ['required', 'string', 'max:255'],
            'field_label' => ['required', 'string', 'max:255'],
            'field_type' => [
                'required',
                Rule::in(self::FIELD_TYPES),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'options' => ['nullable', 'string', 'max:255'],
            'mandatory' => ['required', Rule::in(self::YES_NO)],
            'calling_authority' => ['nullable', Rule::in(self::YES_NO)],
            'show_authority' => ['required', Rule::in(self::YES_NO)],
            'agent_view' => ['required', Rule::in(self::YES_NO)],
            'field_help' => ['nullable', 'string'],
            'field_size' => ['nullable', 'integer'],
            'field_max_length' => ['nullable', 'integer'],
            'search_filter' => ['nullable', Rule::in(self::YES_NO)],
            'editable' => ['required', Rule::in(self::YES_NO)],
            'default_value' => ['nullable', 'string', 'max:255'],
            'value_masked' => ['nullable', Rule::in(self::YES_NO)],
            'duplicate_check' => ['nullable', Rule::in(self::YES_NO)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'group_name' => ['nullable', 'string', 'max:255'],
            'send_to_crm' => ['nullable', Rule::in(self::YES_NO)],
            'allow_field_deletion' => ['nullable', Rule::in(self::YES_NO)],
            'allow_data_delete' => ['nullable', Rule::in(self::YES_NO)],
        ];

        if (config('omni.FIELD_API_ENABLED')) {
            $fieldRules += [
                'uses_api' => ['nullable', Rule::in(self::YES_NO)],
                'api_config_id' => ['required_if:uses_api,Y', 'nullable', Rule::exists('api_configs', 'id')],
                'dependent_on_other' => ['nullable', Rule::in(self::YES_NO)],
                'dependency_mandatory' => ['nullable', Rule::in(self::YES_NO)],
                'response_type' => ['required_if:uses_api,Y', 'nullable', Rule::in(self::RESPONSE_TYPES)],
                'response_data_path' => ['nullable', 'regex:/^[a-zA-Z0-9_.]*$/'],
                'response_label_key' => ['required_if:response_type,array_objects', 'nullable', 'string', 'max:255'],
                'response_value_key' => ['required_if:response_type,array_objects', 'nullable', 'string', 'max:255'],
                'dependency_mappings' => ['sometimes', 'array', 'required_if:dependent_on_other,Y'],
                'dependency_mappings.*.parent_field_name' => ['nullable', 'string', 'max:255'],
                'dependency_mappings.*.param_name' => ['required_with:dependency_mappings', 'string', 'max:100'],
                'dependency_mappings.*.param_type' => ['required_with:dependency_mappings', Rule::in(self::DEPENDENCY_PARAM_TYPES)],
                'dependency_mappings.*.sort_order' => ['nullable', 'integer', 'min:0'],
            ];
        }

        $rules = [
            'list_id' => 'required|exists:ecdial_lists,list_id',
            'form_items' => ['required', 'array'],
            'form_items.*' => ['array'],
        ];

        foreach (self::FORM_COLUMNS as $column) {
            $rules["form_items.$column"] = ['sometimes', 'array'];
            $rules["form_items.$column.*"] = ['array'];

            foreach ($fieldRules as $field => $constraints) {
                $rules["form_items.$column.*.$field"] = $constraints;
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('form_items')) {
            return;
        }

        $legacyFormItems = [];

        foreach (self::FORM_COLUMNS as $column) {
            $value = $this->input($column);

            if (is_array($value)) {
                $legacyFormItems[$column] = $value;
            }
        }

        if (!empty($legacyFormItems)) {
            $this->merge(['form_items' => $legacyFormItems]);
        }
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
