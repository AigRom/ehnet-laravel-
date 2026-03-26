<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // password_confirmation peab olema olemas
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => __('Paroolide kinnitus ei klapi.'),
            'password.min' => __('Parool peab olema vähemalt :min tähemärki pikk.'),
        ];
    }
}