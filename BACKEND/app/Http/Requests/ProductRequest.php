<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $productID = $this->route('product');
        $rules =  [
            'name' => 'required|string|unique:products,name',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ];

        if ($productID) {
            $rules['name'] = 'required|string|unique:products,name,' . $productID;
        }

        return $rules;
    }
}
