@php
    /*
    |--------------------------------------------------------------------------
    | Redirect pärast sisselogimist
    |--------------------------------------------------------------------------
    |
    | Kui kasutaja tuli login-lehele näiteks kaitstud lehelt või kuulutuse
    | lisamise voost, salvestame redirect parameetri sessiooni.
    | Pärast edukat sisselogimist saab Laravel kasutaja õigesse kohta tagasi suunata.
    |
    */
    if (request('redirect')) {
        session(['url.intended' => request('redirect')]);
    }
@endphp

<x-layouts.auth>
    <div class="mx-auto w-full max-w-md">
        <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-6 sm:p-8">
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        {{ __('Logi sisse oma EHNET kontole') }}
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        {{ __('Sisselogimiseks sisesta oma e-post ja parool.') }}
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if (request('notice') === 'create_listing')
                    <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-100">
                        {{ __('Kuulutuse lisamiseks palun logi sisse või loo konto.') }}
                    </div>
                @endif

                @if ($errors->has('email'))
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        {{ __('Sisestatud andmed ei ole õiged. Palun kontrolli ja proovi uuesti.') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('E-post') }}
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                        >
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Parool') }}
                            </label>

                            @if (Route::has('password.request'))
                                <a
                                    href="{{ route('password.request') }}"
                                    wire:navigate
                                    class="text-sm font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300"
                                >
                                    {{ __('Unustasid parooli?') }}
                                </a>
                            @endif
                        </div>

                        <div x-data="{ show: false }" class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                x-bind:type="show ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="{{ __('Sisesta parool') }}"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            <button
                                type="button"
                                x-on:click="show = !show"
                                class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                                x-bind:aria-label="show ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                                x-bind:title="show ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                            >
                                <template x-if="show">
                                    <x-icons.eye class="h-5 w-5" />
                                </template>

                                <template x-if="!show">
                                    <x-icons.eye-off class="h-5 w-5" />
                                </template>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="inline-flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                @checked(old('remember'))
                                class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"
                            >
                            <span>{{ __('Mäleta mind') }}</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        data-test="login-button"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                    >
                        {{ __('Logi sisse') }}
                    </button>
                </form>
            </div>

            @if (Route::has('register'))
                <div class="border-t border-zinc-200 bg-zinc-50 px-6 py-4 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-400 sm:px-8">
                    <span>{{ __('Sul ei ole veel kontot?') }}</span>
                    <a
                        href="{{ route('register') }}"
                        wire:navigate
                        class="ml-1 font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300"
                    >
                        {{ __('Registreeru siin') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.auth>