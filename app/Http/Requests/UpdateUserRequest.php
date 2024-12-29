<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'first_name' => 'sometimes|min:3|string',
            'last_name' => 'sometimes|min:3|string',
            'address' => 'sometimes|string',
            'profile_picture' => 'sometimes|file|mimes:png,jpg,jpeg|max:2048',
        ];
    }
}
