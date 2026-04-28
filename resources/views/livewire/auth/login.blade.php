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
    <div class="w-full">
        <div class="overflow-hidden rounded-[2.25rem] border border-emerald-950/10 bg-white shadow-2xl shadow-emerald-950/10 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-7 sm:p-9 lg:p-12">

                {{-- Header --}}
                <div class="mb-9 text-center lg:mb-11">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-900 text-white shadow-lg shadow-emerald-950/20">
                        <x-icons.login class="h-7 w-7" />
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 dark:text-white lg:text-4xl">
                        {{ __('Logi sisse') }}
                    </h1>
                </div>

                {{-- Status --}}
                @if (session('status'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.42-.004l-4-4a1 1 0 1 1 1.414-1.414l3.294 3.293 7.296-7.289a1 1 0 0 1 1.41 0Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <div>
                            <div class="font-bold">{{ __('Õnnestus') }}</div>
                            <div class="mt-0.5">{{ session('status') }}</div>
                        </div>
                    </div>
                @endif

                {{-- Notice --}}
                @if (request('notice') === 'create_listing')
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-100">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 15.75h.007v.008H12v-.008z" />
                        </svg>

                        <div>
                            <div class="font-bold">{{ __('Kuulutuse lisamiseks') }}</div>
                            <div class="mt-0.5">{{ __('Palun logi sisse või loo konto.') }}</div>
                        </div>
                    </div>
                @endif

                {{-- Error --}}
                @if ($errors->has('email'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                        </svg>

                        <div>
                            <div class="font-bold">{{ __('Kontrolli andmeid') }}</div>
                            <div class="mt-0.5">{{ __('Sisestatud andmed ei ole õiged. Palun kontrolli ja proovi uuesti.') }}</div>
                        </div>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.store') }}"
                    class="space-y-6"
                    novalidate
                    x-data="{
                        emailError: '',
                        passwordError: '',

                        validate() {
                            this.emailError = '';
                            this.passwordError = '';

                            const email = this.$refs.email;
                            const password = this.$refs.password;

                            if (!email.value.trim()) {
                                this.emailError = 'E-posti aadress on kohustuslik.';
                                email.focus();
                                return false;
                            }

                            if (!email.checkValidity()) {
                                this.emailError = 'Sisesta korrektne e-posti aadress.';
                                email.focus();
                                return false;
                            }

                            if (!password.value.trim()) {
                                this.passwordError = 'Parool on kohustuslik.';
                                password.focus();
                                return false;
                            }

                            return true;
                        }
                    }"
                    @submit="if (!validate()) $event.preventDefault()"
                >
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                            {{ __('E-post') }}
                        </label>

                        <input
                            id="email"
                            x-ref="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                            @input="emailError = ''"
                            class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            :class="emailError ? 'border-red-300 bg-red-50/40 focus:border-red-400 focus:ring-red-100' : ''"
                        >

                        <p
                            x-cloak
                            x-show="emailError"
                            x-text="emailError"
                            class="mt-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm font-medium text-red-700"
                        ></p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                {{ __('Parool') }}
                            </label>

                            @if (Route::has('password.request'))
                                <a
                                    href="{{ route('password.request') }}"
                                    wire:navigate
                                    class="text-sm font-bold text-emerald-800 transition hover:text-emerald-900 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300"
                                >
                                    {{ __('Unustasid parooli?') }}
                                </a>
                            @endif
                        </div>

                        <div x-data="{ show: false }" class="relative">
                            <input
                                id="password"
                                x-ref="password"
                                name="password"
                                type="password"
                                x-bind:type="show ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="{{ __('Sisesta parool') }}"
                                @input="passwordError = ''"
                                class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 pr-14 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                                :class="passwordError ? 'border-red-300 bg-red-50/40 focus:border-red-400 focus:ring-red-100' : ''"
                            >

                            <button
                                type="button"
                                x-on:click="show = !show"
                                class="absolute inset-y-0 right-4 inline-flex items-center justify-center text-zinc-500 transition hover:text-emerald-900 dark:text-zinc-400 dark:hover:text-emerald-300"
                                x-bind:aria-label="show ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                                x-bind:title="show ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                            >
                                <template x-if="show">
                                    <x-icons.eye class="h-6 w-6" />
                                </template>

                                <template x-if="!show">
                                    <x-icons.eye-off class="h-6 w-6" />
                                </template>
                            </button>
                        </div>

                        <p
                            x-cloak
                            x-show="passwordError"
                            x-text="passwordError"
                            class="mt-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm font-medium text-red-700"
                        ></p>
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center justify-between gap-4">
                        <label class="inline-flex items-center gap-3 text-base font-medium text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                @checked(old('remember'))
                                class="h-5 w-5 rounded border-zinc-300 text-emerald-900 focus:ring-emerald-900 dark:border-zinc-700 dark:bg-zinc-900"
                            >
                            <span>{{ __('Mäleta mind') }}</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        data-test="login-button"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-4 text-base font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                    >
                        {{ __('Logi sisse') }}
                    </button>
                </form>
            </div>

            @if (Route::has('register'))
                <div class="border-t border-emerald-950/10 bg-emerald-50/60 px-7 py-5 text-center text-base text-zinc-700 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-400 sm:px-9 lg:px-12">
                    <span>{{ __('Sul ei ole veel kontot?') }}</span>

                    <a
                        href="{{ route('register') }}"
                        wire:navigate
                        class="ml-1 font-bold text-emerald-900 transition hover:text-emerald-700 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300"
                    >
                        {{ __('Registreeru siin') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.auth>