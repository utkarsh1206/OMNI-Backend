<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'campaign_id' => 'required|integer',
            'call_back_time' => 'required|date',
            'recipient' => 'required|in:Useronly,Anyone',
            'comments' => 'required|string',
            'user_group' => 'nullable|string',
            'lead_status' => 'nullable|string',
            'last_modified_user' => 'nullable|string',
        ];
    }
}
