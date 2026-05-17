<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {

        $user = $this->user();

        $rules = [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'regex:/^[\pL\pN\s_-]+$/u',
                Rule::unique(User::class, 'name')->ignore($user->id),
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user->id),
            ],

            'phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],

            'location_id' => ['required', 'integer', 'exists:locations,id'],

            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],

            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'company_reg_no' => ['nullable', 'string', 'max:50'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],

            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        if ($user->isCustomer()) {
            $rules['first_name'] = ['required', 'string', 'max:255'];
            $rules['last_name'] = ['required', 'string', 'max:255'];
        }

        if ($user->isBusiness()) {
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['company_reg_no'] = ['required', 'string', 'max:50'];
            $rules['contact_first_name'] = ['required', 'string', 'max:255'];
            $rules['contact_last_name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }
}
