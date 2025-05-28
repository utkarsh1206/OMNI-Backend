<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class TenantLicenseUpdateRequest extends FormRequest
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
            'expire_date' => 'required',
            'agent_ct' => 'required|integer|min:1|max:9999',
            'live_agent_ct' => 'required|integer|min:1|max:9999',
            'manager_ct' => 'required|integer|min:1|max:9999',
        ];
    }

    /**
     * Customize the data before it is returned.
     *
     * @return array
     */
    protected function prepareForValidation()
    {
        // Convert expire_date to epoch timestamp
        if ($this->has('expire_date')) {
                $date = Carbon::createFromFormat('Y-m-d', $this->expire_date);
                $this->merge([
                    'expire_date' => $date->setTime(12, 0)->timestamp,
                ]);
        }
    }
}
