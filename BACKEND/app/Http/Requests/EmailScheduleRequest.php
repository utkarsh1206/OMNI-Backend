<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;


class EmailScheduleRequest extends FormRequest
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
            'report_name' => 'required',
            'selected_columns'     => 'required|array|min:1',
            'selected_columns.*'   => 'string',
            'from_email'           => 'required|email',
            'to_emails'            => 'required|array|min:1',
            'to_emails.*'          => 'email',
            'cc_emails'            => 'nullable|array',
            'cc_emails.*'          => 'email',
            'subject'              => 'required|string|max:255',
            'body'                 => 'required|string',
            'frequency'            => 'required|in:daily,weekly,quarterly,half_yearly,yearly,custom',
            'scheduled_time'       => 'required|date_format:H:i',
            'day_of_week'          => 'nullable|required_if:frequency,weekly|in:mon,tue,wed,thu,fri,sat,sun',
            'data_start_time'      => 'nullable|date_format:H:i',
            'data_end_time'        => 'nullable|date_format:H:i',
            'timezone'             => 'nullable|string'
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
