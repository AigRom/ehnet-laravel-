<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'terms' => ['accepted', 'required'],

            'type' => ['nullable', Rule::in(['customer', 'business'])],

            // EHNET väljad
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],

            'phone'  => ['nullable', 'string', 'max:50'],
            'region' => ['nullable', 'string', 'max:255'],
            'city'   => ['nullable', 'string', 'max:255'],

            'company_name'       => ['nullable', 'string', 'max:255'],
            'company_reg_no'     => ['nullable', 'string', 'max:50'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name'  => ['nullable', 'string', 'max:255'],
        ])->validate();

        // Roll
        $role = User::ROLE_CUSTOMER;
        if (($input['type'] ?? null) === 'business') {
            $role = User::ROLE_BUSINESS;
        }

        return User::create([
            'name'              => $input['name'],
            'email'             => $input['email'],
            'password'          => Hash::make($input['password']),

            // Profiiliväljad
            'first_name'        => $input['first_name'] ?? null,
            'last_name'         => $input['last_name'] ?? null,
            'date_of_birth'     => $input['date_of_birth'] ?? null,

            'phone'             => $input['phone'] ?? null,
            'region'            => $input['region'] ?? null,
            'city'              => $input['city'] ?? null,

            'company_name'      => $input['company_name'] ?? null,
            'company_reg_no'    => $input['company_reg_no'] ?? null,
            'contact_first_name'=> $input['contact_first_name'] ?? null,
            'contact_last_name' => $input['contact_last_name'] ?? null,

            // EHNET süsteemiväljad
            'email_verified_at' => now(),
            'role'              => $role,
            'is_active'         => true,
            'terms_accepted_at' => now(),
            'auth_provider'     => 'email',
            'auth_provider_id'  => null,
            'last_login_at'     => null,
        ]);
    }
}
