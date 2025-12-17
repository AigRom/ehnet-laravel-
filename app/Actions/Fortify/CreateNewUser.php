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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            'password' => $this->passwordRules(),
            'terms' => ['accepted', 'required'],

            'type' => ['required', Rule::in(['customer', 'business'])],

            // Customer (eraisik)
            'first_name' => ['required_if:type,customer', 'nullable', 'string', 'max:255'],
            'last_name'  => ['required_if:type,customer', 'nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],

            // Business (ettevõte)
            'company_name'       => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'company_reg_no'     => ['required_if:type,business', 'nullable', 'string', 'max:50'],
            'contact_first_name' => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'contact_last_name'  => ['required_if:type,business', 'nullable', 'string', 'max:255'],

            // Common
            'phone'       => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ])->validate();

        // Roll
        $role = ($input['type'] === 'business')
            ? User::ROLE_BUSINESS
            : User::ROLE_CUSTOMER;

        // users.name kasutame UI jaoks (sidebar, dropdown jne)
        $displayName = ($input['type'] === 'business')
            ? ($input['company_name'] ?? '')
            : ($input['first_name'] ?? '');

        return User::create([
            // Laravel starter kit eeldab, et "name" on olemas -> täidame automaatselt
            'name'              => $displayName,

            'email'             => $input['email'],
            'password'          => Hash::make($input['password']),

            // Profiiliväljad
            'first_name'        => $input['first_name'] ?? null,
            'last_name'         => $input['last_name'] ?? null,
            'date_of_birth'     => $input['date_of_birth'] ?? null,

            'phone'             => $input['phone'] ?? null,
            'location_id'       => (int) $input['location_id'],

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
