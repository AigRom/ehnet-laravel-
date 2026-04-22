<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @livewireStyles
    </head>

    <body class="min-h-screen antialiased bg-gradient-to-br from-emerald-50 via-white to-emerald-100 dark:bg-gradient-to-b dark:from-zinc-950 dark:to-zinc-900">
        <div class="relative min-h-screen overflow-hidden">
            {{-- Tausta blurid --}}
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl dark:bg-emerald-500/10"></div>
                <div class="absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-lime-200/30 blur-3xl dark:bg-lime-500/10"></div>
                <div class="absolute -bottom-24 left-1/3 h-72 w-72 rounded-full bg-teal-200/30 blur-3xl dark:bg-teal-500/10"></div>
            </div>

            {{-- Logo täpselt nagu navbaris --}}
            <div class="relative z-10">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center">
                        <a
                            href="{{ route('home') }}"
                            class="flex items-center gap-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            wire:navigate
                            title="{{ __('Avalehele') }}"
                        >
                            <x-app-logo class="h-10 w-auto text-emerald-600" />
                        </a>
                    </div>
                </div>
            </div>

            {{-- Keskne vorm --}}
            <div class="relative flex min-h-[calc(100vh-4rem)] flex-col items-center justify-center px-6 py-10">
                <div class="flex w-full max-w-sm flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>