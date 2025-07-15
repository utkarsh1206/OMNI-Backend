<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCustomFieldRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        $fieldRules = [
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
        ];

        return [
            'product_id' => 'required|exists:products,id',
            '*.A' => 'array',
            '*.B' => 'array',
            '*.C' => 'array',
            '*.D' => 'array',
            'form_items.*.A.*' => $fieldRules,
            'form_items.*.B.*' => $fieldRules,
            'form_items.*.C.*' => $fieldRules,
            'form_items.*.D.*' => $fieldRules,
        ];
    }
}
