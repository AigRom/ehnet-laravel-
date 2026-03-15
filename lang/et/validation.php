<?php

return [

    'required' => ':attribute on kohustuslik.',
    'required_if' => ':attribute on kohustuslik.',
    'email' => 'Väljale :attribute tuleb sisestada korrektne e-posti aadress.',
    'min' => [
        'string' => 'Väli :attribute peab sisaldama vähemalt :min tähemärki.',
    ],
    'max' => [
        'string' => 'Väli :attribute ei tohi olla pikem kui :max tähemärki.',
    ],
    'confirmed' => ':attribute ei ühti.',
    'numeric' => 'Väli :attribute peab sisaldama ainult numbreid.',
    'string' => 'Väli :attribute peab olema tekst.',
    'date' => 'Väli :attribute peab olema korrektne kuupäev.',
    'regex' => 'Välja :attribute vorming ei ole õige.',

    'attributes' => [
        'current_password' => 'praegune parool',
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
        'password_confirmation' => 'parooli kinnitus',
        'type' => 'konto tüüp',
        'email' => 'e-post',
    ],

];