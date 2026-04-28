<x-layouts.auth>
    <div class="w-full">
        <div class="overflow-hidden rounded-[2.25rem] border border-emerald-950/10 bg-white shadow-2xl shadow-emerald-950/10 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-7 sm:p-9 lg:p-12">

                {{-- Header --}}
                <div class="mb-9 text-center lg:mb-11">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-900 text-white shadow-lg shadow-emerald-950/20">
                        <x-icons.key class="h-7 w-7" />
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 dark:text-white lg:text-4xl">
                        {{ __('Määra uus parool') }}
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-zinc-600 dark:text-zinc-400 lg:text-lg">
                        {{ __('Sisesta allpool oma uus parool.') }}
                    </p>
                </div>

                {{-- Status --}}
                @if (session('status'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <div class="font-bold">{{ __('Palun kontrolli sisestatud andmeid.') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-7" novalidate>
                    @csrf

                    <input type="hidden" name="token" value="{{ request()->route('token') }}">

                    {{-- E-post --}}
                    <div>
                        <label for="email" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
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
                            class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                        >

                        @error('email')
                            <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Paroolid --}}
                    <div x-data="{ showPassword: false, showConfirm: false }" class="space-y-5">
                        <div>
                            <label for="password" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
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
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 pr-14 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                                >

                                <button
                                    type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-4 inline-flex items-center justify-center text-zinc-500 transition hover:text-emerald-900 dark:text-zinc-400 dark:hover:text-emerald-300"
                                    x-bind:aria-label="showPassword ? 'Peida parool' : 'Näita parooli'"
                                    x-bind:title="showPassword ? 'Peida parool' : 'Näita parooli'"
                                >
                                    <template x-if="showPassword">
                                        <x-icons.eye class="h-6 w-6" />
                                    </template>

                                    <template x-if="!showPassword">
                                        <x-icons.eye-off class="h-6 w-6" />
                                    </template>
                                </button>
                            </div>

                            <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                {{ __('Parool peab olema vähemalt 8 tähemärki pikk.') }}
                            </p>

                            @error('password')
                                <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
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
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 pr-14 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                                >

                                <button
                                    type="button"
                                    x-on:click="showConfirm = !showConfirm"
                                    class="absolute inset-y-0 right-4 inline-flex items-center justify-center text-zinc-500 transition hover:text-emerald-900 dark:text-zinc-400 dark:hover:text-emerald-300"
                                    x-bind:aria-label="showConfirm ? 'Peida parool' : 'Näita parooli'"
                                    x-bind:title="showConfirm ? 'Peida parool' : 'Näita parooli'"
                                >
                                    <template x-if="showConfirm">
                                        <x-icons.eye class="h-6 w-6" />
                                    </template>

                                    <template x-if="!showConfirm">
                                        <x-icons.eye-off class="h-6 w-6" />
                                    </template>
                                </button>
                            </div>

                            <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                {{ __('Sisesta sama parool uuesti.') }}
                            </p>

                            @error('password_confirmation')
                                <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button
                        type="submit"
                        data-test="reset-password-button"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-4 text-base font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                    >
                        {{ __('Salvesta uus parool') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.auth>