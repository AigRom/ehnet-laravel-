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
     * @param array<string, mixed> $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'alpha_dash',
                Rule::unique(User::class, 'name'),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],

            'password' => $this->passwordRules(),
            'terms' => ['accepted', 'required'],

            'type' => ['required', Rule::in(['customer', 'business'])],

            // Customer (eraisik)
            'first_name' => ['required_if:type,customer', 'nullable', 'string', 'max:255'],
            'last_name' => ['required_if:type,customer', 'nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],

            // Business (ettevõte)
            'company_name' => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'company_reg_no' => ['required_if:type,business', 'nullable', 'regex:/^[0-9]{7,20}$/'],
            'contact_first_name' => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'contact_last_name' => ['required_if:type,business', 'nullable', 'string', 'max:255'],

            // Common
            'phone' => ['required', 'string', 'regex:/^\+[0-9]{7,15}$/'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ], [
            'name.required' => 'Kasutajanimi on kohustuslik.',
            'name.min' => 'Kasutajanimi peab olema vähemalt 3 tähemärki pikk.',
            'name.max' => 'Kasutajanimi ei tohi olla pikem kui 40 tähemärki.',
            'name.alpha_dash' => 'Kasutajanimi võib sisaldada ainult tähti, numbreid, sidekriipse ja alakriipse.',
            'name.unique' => 'See kasutajanimi on juba kasutusel.',

            'email.required' => 'E-posti aadress on kohustuslik.',
            'email.email' => 'Sisesta korrektne e-posti aadress.',
            'email.unique' => 'Selle e-posti aadressiga konto on juba olemas.',

            'type.required' => 'Konto tüüp on kohustuslik.',
            'type.in' => 'Valitud konto tüüp ei ole sobiv.',

            'first_name.required_if' => 'Eesnimi on eraisiku konto puhul kohustuslik.',
            'last_name.required_if' => 'Perekonnanimi on eraisiku konto puhul kohustuslik.',
            'date_of_birth.before_or_equal' => 'Sünniaeg ei tohi olla tulevikus.',

            'company_name.required_if' => 'Ettevõtte nimi on ettevõtte konto puhul kohustuslik.',
            'company_reg_no.required_if' => 'Registrikood on ettevõtte konto puhul kohustuslik.',
            'company_reg_no.regex' => 'Registrikood peab sisaldama ainult numbreid ja olema 7 kuni 20 kohta pikk.',
            'contact_first_name.required_if' => 'Kontaktisiku eesnimi on ettevõtte konto puhul kohustuslik.',
            'contact_last_name.required_if' => 'Kontaktisiku perekonnanimi on ettevõtte konto puhul kohustuslik.',

            'phone.required' => 'Telefoni number on kohustuslik.',
            'phone.regex' => 'Telefoni number peab olema kujul + ja 7 kuni 15 numbrit.',

            'location_id.required' => 'Asukoha valimine on kohustuslik.',
            'location_id.exists' => 'Valitud asukohta ei leitud.',

            'terms.accepted' => 'Kasutustingimustega nõustumine on kohustuslik.',
            'terms.required' => 'Kasutustingimustega nõustumine on kohustuslik.',
        ])->validate();

        $role = ($input['type'] === 'business')
            ? User::ROLE_BUSINESS
            : User::ROLE_CUSTOMER;

        return User::create([
            // Kasutaja poolt valitud unikaalne kasutajanimi
            'name' => $input['name'],

            'email' => $input['email'],
            'password' => Hash::make($input['password']),

            // Profiiliväljad
            'first_name' => $input['first_name'] ?? null,
            'last_name' => $input['last_name'] ?? null,
            'date_of_birth' => $input['date_of_birth'] ?? null,

            'phone' => $input['phone'] ?? null,
            'location_id' => (int) $input['location_id'],

            'company_name' => $input['company_name'] ?? null,
            'company_reg_no' => $input['company_reg_no'] ?? null,
            'contact_first_name' => $input['contact_first_name'] ?? null,
            'contact_last_name' => $input['contact_last_name'] ?? null,

            // EHNET süsteemiväljad
            'email_verified_at' => now(),
            'role' => $role,
            'is_active' => true,
            'terms_accepted_at' => now(),
            'auth_provider' => 'email',
            'auth_provider_id' => null,
            'last_login_at' => null,
        ]);
    }
}