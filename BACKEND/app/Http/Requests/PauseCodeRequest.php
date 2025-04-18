<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PauseCodeRequest extends FormRequest
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
        $pauseCodeId = $this->route('pause-code');

        $rules = [
            'code' => 'required|string|unique:pause_codes,code',
            'name' => 'required|string|unique:pause_codes,name',
            'description' => 'nullable|string',
        ];

        // If this is an update request, we need to exclude the current pause code from the unique validation
        if ($pauseCodeId) {
            $rules['code'] = 'required|string|unique:pause_codes,code,' . $pauseCodeId;
            $rules['name'] = 'required|string|unique:pause_codes,name,' . $pauseCodeId;
        }

        return $rules;
    }
}