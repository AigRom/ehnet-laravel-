<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{--
            Ühine <head> plokk:
            Siin laetakse meta-andmed, fondid, Vite CSS/JS ja muu vajalik.
        --}}
        @include('partials.head')

        {{--
            Livewire stiilid.
            Vajalikud Livewire komponentidele ja ka auth-lehtedel Alpine/Livewire
            runtime korrektseks toimimiseks.
        --}}
        @livewireStyles
    </head>

    <body class="min-h-screen bg-zinc-100 antialiased dark:bg-gradient-to-b dark:from-zinc-950 dark:to-zinc-900">
        {{--
            Auth-lehtede peamine ümbris.
            Hoiab login / register / parooli taastamise vaated ekraanil keskel.
        --}}
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6">
            {{--
                Maksimaalne laius auth-vaadete jaoks.
                max-w-3xl sobib nii väiksemale login-kaardile kui ka laiemale
                complete-registration vormile.
            --}}
            <div class="w-full max-w-3xl">
                {{--
                    Logo ja link avalehele.
                    Kuvame auth-vaadete kohal lihtsa brändielemendi.
                --}}
                <div class="mb-8 flex justify-center">
                    <a
                        href="{{ route('home') }}"
                        class="flex flex-col items-center gap-3 font-medium"
                        wire:navigate
                    >
                        {{--
                            Logo konteiner:
                            Hele kaart heledas režiimis, tume kaart tumedas režiimis.
                        --}}
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-zinc-900">
                            <x-app-logo-icon class="size-8 fill-current text-black dark:text-white" />
                        </span>

                        {{--
                            Rakenduse nimi ainult screen reader'ile.
                            Visuaalselt me seda teksti ei kuva.
                        --}}
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                </div>

                {{--
                    Siia renderdatakse konkreetse auth-vaate sisu:
                    näiteks login, register, forgot-password jne.
                --}}
                {{ $slot }}
            </div>
        </div>

        {{--
            Livewire skriptid.
            Need peavad olema enne </body> sulgemist, et Livewire ja sellega kaasnev
            Alpine runtime töötaksid kõigil auth-lehtedel.
        --}}
        @livewireScripts
    </body>
</html>