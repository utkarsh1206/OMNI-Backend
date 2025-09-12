<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreCallBackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_id' => 'required|integer',
            'list_id' => 'required|integer',
            // 'campaign_id' => '',
            'call_back_time' => 'required|date',
            'recipient' => 'required|in:Useronly,Anyone',
            'comments' => 'required|string',
            'user_group' => 'nullable|string',
            'lead_status' => 'nullable|string',
            'last_modified_user' => 'nullable|string',
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
