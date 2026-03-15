@props([
    'heading' => null,
    'subheading' => null,
])

<div class="flex items-start gap-8 max-md:flex-col">
    {{-- Vasak seadete menüü --}}
    <aside class="w-full pb-4 md:w-[220px]">
        <nav class="space-y-2">
            <a
                href="{{ route('profile.edit') }}"
                wire:navigate
                class="{{ request()->routeIs('profile.edit')
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
                }} flex items-center rounded-2xl border px-4 py-3 text-sm font-medium transition dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                {{ __('Profiil') }}
            </a>

            <a
                href="{{ route('user-password.edit') }}"
                wire:navigate
                class="{{ request()->routeIs('user-password.edit')
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
                }} flex items-center rounded-2xl border px-4 py-3 text-sm font-medium transition dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                {{ __('Parool') }}
            </a>

            {{--
                Kahefaktorilise autentimise link hilisemaks kasutuseks.
                Kui 2FA kunagi aktiveeritakse, saab selle ploki taastada.
            --}}
            {{--
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <a
                    href="{{ route('two-factor.show') }}"
                    wire:navigate
                    class="{{ request()->routeIs('two-factor.show')
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                        : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
                    }} flex items-center rounded-2xl border px-4 py-3 text-sm font-medium transition dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-white"
                >
                    {{ __('Kaheastmeline autentimine') }}
                </a>
            @endif
            --}}

            
        </nav>
    </aside>

    {{-- Mobiilivaates eraldaja --}}
    <div class="h-px w-full bg-zinc-200 md:hidden dark:bg-zinc-800"></div>

    {{-- Parempoolne sisu --}}
    <section class="flex-1 self-stretch max-md:pt-2">
        @if ($heading)
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ $heading }}
            </h1>
        @endif

        @if ($subheading)
            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                {{ $subheading }}
            </p>
        @endif

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </section>
</div>