<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SipServerConfigurationRequest extends FormRequest
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
            'sip_server_url' => ['required', 'string'],
            'sip_ua_uri_domain' => ['required', 'string'],
        ];
    }
}
