<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',

                Rule::unique('users', 'email')
                    ->ignore($this->user()->id),
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(09\d{9}|\+639\d{9})$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' =>
                'The first name is required.',

            'last_name.required' =>
                'The last name is required.',

            'email.required' =>
                'The email address is required.',

            'email.email' =>
                'Enter a valid email address.',

            'email.unique' =>
                'This email address is already registered.',

            'contact_number.regex' =>
                'Enter a valid Philippine mobile number, such as 09XXXXXXXXX.',
        ];
    }
}