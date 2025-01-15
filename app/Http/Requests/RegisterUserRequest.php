<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'first_name' => 'required|min:3|string',
            'last_name' => 'required|min:3|string',
            'phone' => 'required|string|regex:/^09[0-9]{8}$/|unique:users,phone|digits:10',
            'password' => 'required|min:8|string|confirmed',
            'address' => 'nullable|string',
            'profile_picture' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'role' => 'nullable|in:admin,store_owner,customer'
        ];
    }
}
