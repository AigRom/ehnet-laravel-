<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteRegistrationRequest extends FormRequest
{
    /**
     * Lubame kõigil seda requesti kasutada
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Enne valideerimist puhastame sisendit
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        // eemaldame telefonist kõik mitte-numbrid
        if ($phone !== null) {
            $phone = preg_replace('/\D+/', '', $phone);
        }

        $this->merge([
            'phone' => $phone,

            // trim eemaldab alguse/lõpu tühikud
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'first_name' => is_string($this->input('first_name')) ? trim($this->input('first_name')) : $this->input('first_name'),
            'last_name' => is_string($this->input('last_name')) ? trim($this->input('last_name')) : $this->input('last_name'),

            'contact_first_name' => is_string($this->input('contact_first_name')) ? trim($this->input('contact_first_name')) : $this->input('contact_first_name'),
            'contact_last_name' => is_string($this->input('contact_last_name')) ? trim($this->input('contact_last_name')) : $this->input('contact_last_name'),

            'company_name' => is_string($this->input('company_name')) ? trim($this->input('company_name')) : $this->input('company_name'),
            'company_reg_no' => is_string($this->input('company_reg_no')) ? trim($this->input('company_reg_no')) : $this->input('company_reg_no'),
        ]);
    }

    /**
     * Valideerimisreeglid
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Konto tüüp
            |--------------------------------------------------------------------------
            */

            'type' => ['required', Rule::in(['customer', 'business'])],

            /*
            |--------------------------------------------------------------------------
            | Kasutajanimi
            |--------------------------------------------------------------------------
            | Peab olema unikaalne ja koosnema lubatud märkidest
            */

            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'alpha_dash',
                'unique:users,name',
            ],

            /*
            |--------------------------------------------------------------------------
            | Eraisiku väljad
            |--------------------------------------------------------------------------
            */

            'first_name' => [
                'required_if:type,customer',
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required_if:type,customer',
                'nullable',
                'string',
                'max:100',
            ],

            'date_of_birth' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ettevõtte väljad
            |--------------------------------------------------------------------------
            */

            'contact_first_name' => [
                'required_if:type,business',
                'nullable',
                'string',
                'max:100',
            ],

            'contact_last_name' => [
                'required_if:type,business',
                'nullable',
                'string',
                'max:100',
            ],

            'company_name' => [
                'required_if:type,business',
                'nullable',
                'string',
                'max:150',
            ],

            'company_reg_no' => [
                'required_if:type,business',
                'nullable',
                'regex:/^[0-9]{7,20}$/',
            ],

            /*
            |--------------------------------------------------------------------------
            | Telefoni number
            |--------------------------------------------------------------------------
            */

            'phone' => [
                'required',
                'regex:/^[0-9]{7,15}$/',
            ],

            /*
            |--------------------------------------------------------------------------
            | Asukoht
            |--------------------------------------------------------------------------
            */

            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Parool
            |--------------------------------------------------------------------------
            */

            'password' => [
                'required',
                'string',
                'min:8',
                'max:100',
                'confirmed',
            ],

            'password_confirmation' => [
                'required',
                'same:password',
            ],
        ];
    }

    /**
     * Eestikeelsed veateated
     */
    public function messages(): array
    {
        return [

            'type.required' => 'Konto tüüp on kohustuslik.',
            'type.in' => 'Valitud konto tüüp ei ole sobiv.',

            'name.required' => 'Kasutajanimi on kohustuslik.',
            'name.min' => 'Kasutajanimi peab olema vähemalt 3 tähemärki pikk.',
            'name.max' => 'Kasutajanimi ei tohi olla pikem kui 40 tähemärki.',
            'name.alpha_dash' => 'Kasutajanimi võib sisaldada ainult tähti, numbreid, sidekriipse ja alakriipse.',
            'name.unique' => 'See kasutajanimi on juba kasutusel.',

            'first_name.required_if' => 'Eesnimi on eraisiku konto puhul kohustuslik.',
            'last_name.required_if' => 'Perekonnanimi on eraisiku konto puhul kohustuslik.',

            'date_of_birth.date' => 'Sünniaeg peab olema korrektne kuupäev.',
            'date_of_birth.before_or_equal' => 'Sünniaeg ei tohi olla tulevikus.',

            'contact_first_name.required_if' => 'Kontaktisiku eesnimi on ettevõtte konto puhul kohustuslik.',
            'contact_last_name.required_if' => 'Kontaktisiku perekonnanimi on ettevõtte konto puhul kohustuslik.',

            'company_name.required_if' => 'Ettevõtte nimi on ettevõtte konto puhul kohustuslik.',

            'company_reg_no.required_if' => 'Registrikood on ettevõtte konto puhul kohustuslik.',
            'company_reg_no.regex' => 'Registrikood peab sisaldama ainult numbreid ja olema 7 kuni 20 kohta pikk.',

            'phone.required' => 'Telefoni number on kohustuslik.',
            'phone.regex' => 'Telefoni number peab sisaldama ainult numbreid ja olema 7 kuni 15 kohta pikk.',

            'location_id.required' => 'Asukoha valimine on kohustuslik.',
            'location_id.exists' => 'Valitud asukohta ei leitud.',

            'password.required' => 'Parool on kohustuslik.',
            'password.min' => 'Parool peab olema vähemalt 8 tähemärki pikk.',
            'password.max' => 'Parool ei tohi olla pikem kui 100 tähemärki.',
            'password_confirmation.required' => 'Parooli kinnitus on kohustuslik.',
            'password_confirmation.same' => 'Paroolid ei kattu.',
            'password.confirmed' => 'Paroolid ei kattu.',
        ];
    }

    /**
     * Väljade inimloetavad nimed
     */
    public function attributes(): array
    {
        return [
            'name' => 'kasutajanimi',
            'first_name' => 'eesnimi',
            'last_name' => 'perekonnanimi',
            'date_of_birth' => 'sünniaeg',
            'contact_first_name' => 'kontaktisiku eesnimi',
            'contact_last_name' => 'kontaktisiku perekonnanimi',
            'company_name' => 'ettevõtte nimi',
            'company_reg_no' => 'registrikood',
            'phone' => 'telefoni number',
            'location_id' => 'asukoht',
            'password' => 'parool',
        ];
    }
}