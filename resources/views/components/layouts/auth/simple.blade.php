<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{--
            Ühine <head> plokk:
            sisaldab meta-andmeid, fondi, Vite CSS/JS ja muud vajalikku.
        --}}
        @include('partials.head')

        {{--
            Livewire stiilid.
        --}}
        @livewireStyles
    </head>

    <body class="min-h-screen bg-white antialiased dark:bg-gradient-to-b dark:from-zinc-950 dark:to-zinc-900">
        {{--
            Auth-lehtede keskne ümbris.
            Keskendab sisu nii vertikaalselt kui horisontaalselt.
        --}}
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-10">
            {{--
                Kitsam konteiner väiksematele auth-vaadetele:
                näiteks login, register, forgot-password.
            --}}
            <div class="flex w-full max-w-sm flex-col gap-6">
                {{--
                    Logo ja link avalehele.
                    Jätame auth-vaadete kohale lihtsa brändiankru.
                --}}
                <a
                    href="{{ route('home') }}"
                    class="flex flex-col items-center gap-2 font-medium"
                    wire:navigate
                >
                    {{--
                        Logo konteiner.
                    --}}
                    <span class="mb-1 flex h-10 w-10 items-center justify-center rounded-xl">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>

                    {{--
                        Screen readeri jaoks rakenduse nimi.
                    --}}
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>

                {{--
                    Siia renderdatakse konkreetse auth-vaate sisu.
                --}}
                {{ $slot }}
            </div>
        </div>

        {{--
            Livewire skriptid.
        --}}
        @livewireScripts
    </body>
</html>