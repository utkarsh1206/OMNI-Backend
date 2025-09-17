<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTemplateMapRequest extends FormRequest
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
            'list_id' => 'required|exists:ecdial_lists,list_id',
            'template_ids'   => 'sometimes|array|min:0',
            'template_ids.*' => 'exists:mail_template,id',
        ];
    }
}
