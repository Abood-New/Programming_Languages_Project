<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
            'product_name' => 'required|string',
            'description' => 'required|string',
            'product_image' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
            'available_quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'category' => 'required|string'
        ];
    }
}
