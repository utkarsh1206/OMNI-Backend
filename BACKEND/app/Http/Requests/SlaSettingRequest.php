<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlaSettingRequest extends FormRequest
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
            'sla.name' => 'required|string|max:100',
            'sla.description' => 'nullable|string|max:255',
            // 'sla.is_24_hours' => "required|in:([1,0,true,'false',false])",
            'sla.is_24_hours' => "required|boolean",
            'sla.start_time' => 'nullable|date_format:H:i:s',
            'sla.end_time' => 'nullable|date_format:H:i:s',

            'off_days' => 'nullable|array', // off_days is optional, but if provided, must be an array
            'off_days.*' => 'required_if:off_days,*|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun', // Validate individual days only if off_days is not empty


            'holidays' => 'nullable|array',
            'holidays.*.holiday_date' => 'required_if:holidays.*,.*|date', // Only required if holidays are provided
            'holidays.*.description' => 'nullable|string|max:255',

            'targets' => 'required|array',
            'targets.*' => 'required|array',  // Ensure each target group is an array
            'targets.*.*.priority_type' => 'required|integer',
            'targets.*.*.response_time_value' => 'required|integer|min:1',
            'targets.*.*.alert_before_response_value' => 'required|integer|min:1',
            'targets.*.*.resolve_time_value' => 'required|integer|min:1',
            'targets.*.*.alert_before_resolve_value' => 'required|integer|min:1',
        ];
    }
}
