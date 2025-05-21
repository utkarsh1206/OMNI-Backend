<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MailTemplateRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mail_template', 'title')->ignore($this->route('mail_template'))
            ],
            'type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'mail_for' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'in:Active,Inactive',
        ];
    }
}
