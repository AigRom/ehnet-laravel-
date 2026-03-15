{{--
    Dokumendi märgistik.
    UTF-8 tagab korrektse täpitähtede ja muude sümbolite kuvamise.
--}}
<meta charset="utf-8" />

{{--
    Mobiilivaate seadistus.
    Tagab, et leht skaleerub telefonis õigesti.
--}}
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

{{--
    Lehe pealkiri.
    Kui vaade annab $title väärtuse, kasutatakse seda,
    muidu kasutatakse rakenduse nime config/app.php failist.
--}}
<title>{{ $title ?? config('app.name') }}</title>

{{--
    Favikonid eri seadmetele ja brauseritele.
--}}
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{--
    Fondi eelühendus ja fondi laadimine.
    Hetkel kasutatakse Instrument Sans kirjatüüpi Bunny Fonts teenusest.
--}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{--
    Projekti peamine CSS ja JavaScript läbi Vite.
    Siit laetakse Tailwind, Alpine, Livewire integratsioonid jne.
--}}
@vite(['resources/css/app.css', 'resources/js/app.js'])