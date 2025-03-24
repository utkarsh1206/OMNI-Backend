<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneRequest extends FormRequest
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
        
        $phoneId = $this->route('phone');

        return [
            'extension' => $this->isMethod('post')
                ? 'required|unique:phones,extension' // Add: check uniqueness when adding
                : "required|unique:phones,extension,{$phoneId}", // Update: ignore unique check for current record
            
            'login' => $this->isMethod('post')
                ? 'required|unique:phones,login|alpha_num|between:2,320' // Add: check uniqueness when adding
                : "required|unique:phones,login,{$phoneId}|alpha_num|between:2,320", // Update: ignore unique check for current record
            
            'pass' => 'required|alpha_num|between:2,320',
            'status' => 'required',
            'local_gmt' => 'nullable|numeric',
        ];
    }
}
