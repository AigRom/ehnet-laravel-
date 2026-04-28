<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @livewireStyles
    </head>

    <body class="min-h-screen antialiased bg-gradient-to-br from-stone-100 via-emerald-50 to-lime-50 text-zinc-900 dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-900 dark:text-zinc-100">
        {{-- Üldine toast auth lehtede jaoks --}}
        
        <div class="relative min-h-screen overflow-hidden">
            {{-- Tausta blurid --}}
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-28 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-emerald-200/60 blur-3xl dark:bg-emerald-500/10"></div>
                <div class="absolute top-1/3 -right-24 h-96 w-96 rounded-full bg-lime-200/45 blur-3xl dark:bg-lime-500/10"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-teal-200/35 blur-3xl dark:bg-teal-500/10"></div>
            </div>

            {{-- Header --}}
            <header class="relative z-10">
                <div class="mx-auto flex h-24 max-w-[1500px] items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center gap-4 rounded-xl focus:outline-none focus:ring-0"
                        wire:navigate
                        title="{{ __('Avalehele') }}"
                    >
                        <x-app-logo class="h-14 w-auto text-emerald-700 sm:h-16" />

                        <span class="text-2xl font-extrabold tracking-tight text-emerald-950 dark:text-white sm:text-3xl">
                            EHNET
                        </span>
                    </a>

                    <a
                        href="{{ route('home') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white/80 px-4 py-2.5 text-sm font-bold text-emerald-950 shadow-sm backdrop-blur transition hover:bg-white hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 sm:px-5"
                    >
                        {{ __('Avalehele') }}
                    </a>
                </div>
            </header>

            {{-- Keskne vorm --}}
            <main class="relative z-10 flex min-h-[calc(100vh-6rem)] items-start justify-center px-4 pb-12 pt-6 sm:px-6 sm:pt-8 lg:px-8 lg:pb-16">
                <div class="w-full max-w-md sm:max-w-xl lg:max-w-2xl">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
    </body>
</html>