<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        $categoryId = $this->route('category');

        $rules = [
            'name' => 'required|string|unique:categories,name',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ];

        // If this is an update request, we need to exclude the current category from the unique validation
        if ($categoryId) {
            $rules['name'] = 'required|string|unique:categories,name,' . $categoryId;
        }

        return $rules;
    }

}
