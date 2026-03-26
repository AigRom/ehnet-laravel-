<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Üldised valideerimissõnumid
    |--------------------------------------------------------------------------
    */

    'required' => ':attribute on kohustuslik.',
    'required_if' => ':attribute on kohustuslik.',
    'email' => 'Sisesta korrektne e-posti aadress.',
    'min' => [
        'string' => ':attribute peab sisaldama vähemalt :min tähemärki.',
        'numeric' => ':attribute peab olema vähemalt :min.',
    ],
    'max' => [
        'string' => ':attribute ei tohi olla pikem kui :max tähemärki.',
        'numeric' => ':attribute ei tohi olla suurem kui :max.',
        'array' => ':attribute võib sisaldada maksimaalselt :max elementi.',
        'file' => ':attribute võib olla maksimaalselt :max kB.',
    ],
    'confirmed' => ':attribute ei ühti.',
    'numeric' => ':attribute peab olema number.',
    'string' => ':attribute peab olema tekst.',
    'date' => ':attribute peab olema korrektne kuupäev.',
    'regex' => ':attribute formaat ei ole korrektne.',
    'exists' => 'Valitud :attribute ei ole sobiv.',
    'in' => 'Valitud :attribute ei ole sobiv.',
    'array' => ':attribute peab olema loend.',
    'image' => ':attribute peab olema pildifail.',
    'mimes' => ':attribute peab olema tüübiga: :values.',
    'file' => ':attribute peab olema fail.',
    'uploaded' => ':attribute üleslaadimine ebaõnnestus.',

    /*
    |--------------------------------------------------------------------------
    | Kohandatud sõnumid konkreetsetele väljadele
    |--------------------------------------------------------------------------
    */

    'custom' => [

        // Pildid
        'images' => [
            'max' => 'Lisada saab maksimaalselt 10 pilti.',
            'array' => 'Pildid peavad olema loend.',
        ],

        'images.*' => [
            'image' => 'Iga lisatud fail peab olema pilt.',
            'mimes' => 'Pildid peavad olema jpg, jpeg, png või webp formaadis.',
            'max' => 'Iga pildi maksimaalne suurus on 5 MB.',
        ],

        // Kättesaamine
        'delivery_options.*' => [
            'in' => 'Valitud kättesaamise viis ei ole sobiv.',
        ],

        // Price
        'price' => [
            'numeric' => 'Hind peab olema number.',
            'min' => 'Hind ei tohi olla väiksem kui 0.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Väljade inimloetavad nimed
    |--------------------------------------------------------------------------
    */

    'attributes' => [

        // Kasutaja
        'current_password' => 'praegune parool',
        'name' => 'kasutajanimi',
        'first_name' => 'eesnimi',
        'last_name' => 'perekonnanimi',
        'date_of_birth' => 'sünniaeg',
        'contact_first_name' => 'kontaktisiku eesnimi',
        'contact_last_name' => 'kontaktisiku perekonnanimi',
        'company_name' => 'ettevõtte nimi',
        'company_reg_no' => 'registrikood',
        'phone' => 'telefoninumber',
        'password' => 'parool',
        'password_confirmation' => 'parooli kinnitus',
        'type' => 'konto tüüp',
        'email' => 'e-post',
        

        // Kuulutus
        'title' => 'pealkiri',
        'description' => 'kirjeldus',
        'category_id' => 'kategooria',
        'location_id' => 'asukoht',
        'price' => 'hind',
        'price_mode' => 'hinna tüüp',
        'condition' => 'seisukord',
        'delivery_options' => 'kättesaamine',
        'delivery_options.*' => 'kättesaamise valik',
        'images' => 'pildid',
        'images.*' => 'pilt',
        'images_order' => 'piltide järjestus',
        'action' => 'tegevus',

        // Report
        'reason' => 'põhjus',
        'details' => 'selgitus',
    ],

];