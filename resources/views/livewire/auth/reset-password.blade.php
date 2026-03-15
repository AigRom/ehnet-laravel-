@php
    $invalidResetLink = collect($errors->get('email'))->contains(__('See parooli lähtestamise link on kehtetu.'));
@endphp

<x-layouts.auth>
    <div class="mx-auto w-full max-w-md">
        <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-6 sm:p-8">
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        {{ __('Määra uus parool') }}
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        {{ __('Sisesta allpool oma uus parool.') }}
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <div class="font-medium">{{ __('Palun kontrolli sisestatud andmeid.') }}</div>
                    </div>
                @endif

                                {{-- 
                    TODO: Parooli lähtestamise lingi kehtivuse kontroll tuleks tulevikus
                    viia controlleri tasemele.

                    Praegu kontrollime Blade vaates, kas reset token on kehtetu või aegunud,
                    ja peidame vormi kui ilmneb vastav veateade.

                    Õigem arhitektuur oleks:
                    - kontrollida tokeni kehtivust controlleris
                    - kui token on aegunud või kehtetu:
                        * suunata kasutaja tagasi password.request lehele
                        * kuvada seal teade, et link on aegunud
                    - mitte renderdada reset-password vormi üldse.

                    See muudaks vaate lihtsamaks ja loogika oleks backendis.
                --}}

                @if ($invalidResetLink)
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('E-post') }}
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', request('email')) }}"
                                disabled
                                class="block w-full rounded-2xl border border-zinc-200 bg-zinc-100 px-4 py-3 text-sm text-zinc-500 shadow-sm outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400"
                            >

                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <a
                            href="{{ route('password.request') }}"
                            wire:navigate
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                        >
                            {{ __('Küsi uus parooli lähtestamise link') }}
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-6" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ request()->route('token') }}">

                        {{-- E-post --}}
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('E-post') }}
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', request('email')) }}"
                                required
                                autocomplete="email"
                                placeholder="email@example.com"
                                oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'E-posti aadress on kohustuslik.' : 'Sisesta korrektne e-posti aadress.')"
                                oninput="this.setCustomValidity('')"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Paroolid --}}
                        <div x-data="{ showPassword: false, showConfirm: false }" class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ __('Uus parool') }}
                                </label>

                                <div class="relative">
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        x-bind:type="showPassword ? 'text' : 'password'"
                                        required
                                        autocomplete="new-password"
                                        minlength="8"
                                        maxlength="100"
                                        placeholder="{{ __('Sisesta uus parool') }}"
                                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                                    >

                                    <button
                                        type="button"
                                        x-on:click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                                        x-bind:aria-label="showPassword ? 'Peida parool' : 'Näita parooli'"
                                        x-bind:title="showPassword ? 'Peida parool' : 'Näita parooli'"
                                    >
                                        <template x-if="showPassword">
                                            <x-icons.eye class="h-5 w-5" />
                                        </template>

                                        <template x-if="!showPassword">
                                            <x-icons.eye-off class="h-5 w-5" />
                                        </template>
                                    </button>
                                </div>

                                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Parool peab olema vähemalt 8 tähemärki pikk.') }}
                                </p>

                                @error('password')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ __('Korda uut parooli') }}
                                </label>

                                <div class="relative">
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        x-bind:type="showConfirm ? 'text' : 'password'"
                                        required
                                        autocomplete="new-password"
                                        minlength="8"
                                        maxlength="100"
                                        placeholder="{{ __('Korda uut parooli') }}"
                                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                                    >

                                    <button
                                        type="button"
                                        x-on:click="showConfirm = !showConfirm"
                                        class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                                        x-bind:aria-label="showConfirm ? 'Peida parool' : 'Näita parooli'"
                                        x-bind:title="showConfirm ? 'Peida parool' : 'Näita parooli'"
                                    >
                                        <template x-if="showConfirm">
                                            <x-icons.eye class="h-5 w-5" />
                                        </template>

                                        <template x-if="!showConfirm">
                                            <x-icons.eye-off class="h-5 w-5" />
                                        </template>
                                    </button>
                                </div>

                                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Sisesta sama parool uuesti.') }}
                                </p>

                                @error('password_confirmation')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button
                            type="submit"
                            data-test="reset-password-button"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                        >
                            {{ __('Salvesta uus parool') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layouts.auth>