<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EcdialListRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'campaign_id' => 'nullable|string',
        ];
    }
}
