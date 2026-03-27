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
                }} flex items-center rounded-2xl border px-4 py-3 text-sm font-medium transition"
            >
                {{ __('Profiil') }}
            </a>

            <a
                href="{{ route('user-password.edit') }}"
                wire:navigate
                class="{{ request()->routeIs('user-password.edit')
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900'
                }} flex items-center rounded-2xl border px-4 py-3 text-sm font-medium transition"
            >
                {{ __('Parool') }}
            </a>

            <a
                href="{{ route('profile.delete') }}"
                wire:navigate
                class="{{ request()->routeIs('profile.delete')
                    ? 'bg-red-50 text-red-700 border-red-200'
                    : 'bg-white text-zinc-700 border-zinc-200 hover:bg-red-50 hover:text-red-700'
                }} flex items-center rounded-2xl border px-4 py-3 text-sm font-medium transition"
            >
                {{ __('Kustuta konto') }}
            </a>

            {{-- 2FA (hilisemaks kasutuseks) --}}
            {{--
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <a href="{{ route('two-factor.show') }}">
                    {{ __('Kaheastmeline autentimine') }}
                </a>
            @endif
            --}}
        </nav>
    </aside>

    {{-- Mobiilivaates eraldaja --}}
    <div class="h-px w-full bg-zinc-200 md:hidden"></div>

    {{-- Parempoolne sisu --}}
    <section class="flex-1 self-stretch max-md:pt-2">
        @if ($heading)
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
                {{ $heading }}
            </h1>
        @endif

        @if ($subheading)
            <p class="mt-2 text-sm leading-6 text-zinc-600">
                {{ $subheading }}
            </p>
        @endif

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </section>
</div>