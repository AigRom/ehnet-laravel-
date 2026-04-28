<x-layouts.auth>
    <div class="w-full">
        <div class="overflow-hidden rounded-[2.25rem] border border-emerald-950/10 bg-white shadow-2xl shadow-emerald-950/10 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-7 sm:p-9 lg:p-12">

                {{-- Header --}}
                <div class="mb-9 text-center lg:mb-11">
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-900 text-white shadow-lg shadow-emerald-950/20">
                        <x-icons.user-plus class="h-7 w-7" />
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 dark:text-white lg:text-4xl">
                        {{ __('Vii oma EHNET konto registreerimine lõpuni') }}
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-zinc-600 dark:text-zinc-400 lg:text-lg">
                        {{ __('Täida nõutud väljad, et saaksid EHNET kontot kasutama hakata.') }}
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

                <form method="POST" action="{{ route('register.complete.post', $token) }}" class="space-y-7" id="completeRegForm" novalidate>
                    @csrf

                    {{-- E-post --}}
                    <div>
                        <label for="email" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                            {{ __('E-post') }}
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ $email }}"
                            disabled
                            class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-100 px-5 py-4 text-base font-medium text-zinc-500 shadow-sm outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400"
                        >
                    </div>

                    {{-- Kasutajanimi --}}
                    <div>
                        <label for="name" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                            {{ __('Kasutajanimi') }}
                        </label>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            minlength="3"
                            maxlength="25"
                            autocomplete="nickname"
                            spellcheck="false"
                            placeholder="{{ __('Näiteks: MatiK või Ehnet OÜ') }}"
                            class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                        >

                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            {{ __('See nimi kuvatakse sinu profiilil ja kuulutuste juures. Soovituslik pikkus 3–25 tähemärki.') }}
                        </p>

                        @error('name')
                            <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konto tüüp + vastavad väljad --}}
                    <div x-data="{ accountType: '{{ old('type', 'customer') }}' }" class="space-y-7">
                        <div>
                            <p class="mb-3 text-base font-bold text-emerald-950 dark:text-zinc-100">
                                {{ __('Konto tüüp') }}
                            </p>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-2xl border px-5 py-4 text-base transition dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200"
                                    :class="accountType === 'customer'
                                        ? 'border-emerald-900/30 bg-emerald-50 text-emerald-950 ring-4 ring-emerald-900/10'
                                        : 'border-emerald-950/10 bg-stone-50 text-zinc-700 hover:border-emerald-900/30 hover:bg-white'"
                                >
                                    <input
                                        type="radio"
                                        name="type"
                                        value="customer"
                                        x-model="accountType"
                                        class="mt-1 h-5 w-5 border-zinc-300 text-emerald-900 focus:ring-emerald-900 dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                    <span>
                                        <span class="block font-bold">{{ __('Eraisik') }}</span>
                                    </span>
                                </label>

                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-2xl border px-5 py-4 text-base transition dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200"
                                    :class="accountType === 'business'
                                        ? 'border-emerald-900/30 bg-emerald-50 text-emerald-950 ring-4 ring-emerald-900/10'
                                        : 'border-emerald-950/10 bg-stone-50 text-zinc-700 hover:border-emerald-900/30 hover:bg-white'"
                                >
                                    <input
                                        type="radio"
                                        name="type"
                                        value="business"
                                        x-model="accountType"
                                        class="mt-1 h-5 w-5 border-zinc-300 text-emerald-900 focus:ring-emerald-900 dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                    <span>
                                        <span class="block font-bold">{{ __('Ettevõte') }}</span>
                                    </span>
                                </label>
                            </div>

                            @error('type')
                                <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Eraisiku väljad --}}
                        <div
                            id="privateFields"
                            x-show="accountType === 'customer'"
                            x-cloak
                            class="grid gap-5 sm:grid-cols-2"
                        >
                            <div>
                                <label for="first_name" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Eesnimi') }}
                                </label>

                                <input
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    value="{{ old('first_name') }}"
                                    autocomplete="given-name"
                                    placeholder="{{ __('Sisesta eesnimi') }}"
                                    maxlength="100"
                                    x-bind:disabled="accountType !== 'customer'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ __('Kasuta oma päris eesnime.') }}
                                </p>

                                @error('first_name')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_name" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Perekonnanimi') }}
                                </label>

                                <input
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old('last_name') }}"
                                    autocomplete="family-name"
                                    placeholder="{{ __('Sisesta perekonnanimi') }}"
                                    maxlength="100"
                                    x-bind:disabled="accountType !== 'customer'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ __('Kasuta oma päris perekonnanime.') }}
                                </p>

                                @error('last_name')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- TODO: tulevikus asendada native date input eestikeelse datepickeriga --}}
                            <div class="sm:col-span-2">
                                <label for="date_of_birth" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Sünniaeg (valikuline)') }}
                                </label>

                                <input
                                    id="date_of_birth"
                                    name="date_of_birth"
                                    type="date"
                                    value="{{ old('date_of_birth') }}"
                                    max="{{ now()->toDateString() }}"
                                    x-bind:disabled="accountType !== 'customer'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ __('Seda välja ei pea täitma, kui sa ei soovi.') }}
                                </p>

                                @error('date_of_birth')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Ettevõtte väljad --}}
                        <div
                            id="businessFields"
                            x-show="accountType === 'business'"
                            x-cloak
                            class="grid gap-5 sm:grid-cols-2"
                        >
                            <div>
                                <label for="contact_first_name" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Kontaktisiku eesnimi') }}
                                </label>

                                <input
                                    id="contact_first_name"
                                    name="contact_first_name"
                                    type="text"
                                    value="{{ old('contact_first_name') }}"
                                    autocomplete="given-name"
                                    placeholder="{{ __('Sisesta kontaktisiku eesnimi') }}"
                                    maxlength="100"
                                    x-bind:disabled="accountType !== 'business'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                @error('contact_first_name')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact_last_name" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Kontaktisiku perekonnanimi') }}
                                </label>

                                <input
                                    id="contact_last_name"
                                    name="contact_last_name"
                                    type="text"
                                    value="{{ old('contact_last_name') }}"
                                    autocomplete="family-name"
                                    placeholder="{{ __('Sisesta kontaktisiku perekonnanimi') }}"
                                    maxlength="100"
                                    x-bind:disabled="accountType !== 'business'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                @error('contact_last_name')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="company_name" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Ettevõtte nimi') }}
                                </label>

                                <input
                                    id="company_name"
                                    name="company_name"
                                    type="text"
                                    value="{{ old('company_name') }}"
                                    placeholder="{{ __('Sisesta ettevõtte nimi') }}"
                                    maxlength="150"
                                    x-bind:disabled="accountType !== 'business'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ __('Sisesta ettevõtte ametlik nimi.') }}
                                </p>

                                @error('company_name')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="company_reg_no" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                    {{ __('Registrikood') }}
                                </label>

                                <input
                                    id="company_reg_no"
                                    name="company_reg_no"
                                    type="text"
                                    value="{{ old('company_reg_no') }}"
                                    placeholder="{{ __('Näiteks 12345678') }}"
                                    inputmode="numeric"
                                    maxlength="20"
                                    pattern="[0-9]+"
                                    x-bind:disabled="accountType !== 'business'"
                                    class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-4 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 disabled:bg-zinc-100 disabled:text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30 dark:disabled:bg-zinc-900"
                                >

                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ __('Kasuta ainult numbreid, ilma tühikute ja sidekriipsudeta.') }}
                                </p>

                                @error('company_reg_no')
                                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Telefoni number --}}
                    <div>
                        <label for="phone" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                            {{ __('Telefoni number') }}
                        </label>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-base font-bold text-zinc-500">
                                +
                            </span>

                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                value="{{ old('phone') }}"
                                required
                                autocomplete="tel"
                                inputmode="numeric"
                                pattern="[0-9]{7,15}"
                                minlength="7"
                                maxlength="15"
                                placeholder="{{ __('37251234567') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="block w-full rounded-2xl border border-emerald-950/10 bg-stone-50 py-4 pl-10 pr-5 text-base font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >
                        </div>

                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            {{ __('Sisesta telefoninumber koos riigikoodiga, ilma + märgita.') }}
                        </p>

                        @error('phone')
                            <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Asukoht --}}
                    <div class="relative overflow-visible">
                        <livewire:location-autocomplete :initial-id="old('location_id')" :wire:key="'loc-'.(old('location_id') ?? 'new')" />

                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            {{ __('Vali oma asukoht nimekirjast.') }}
                        </p>
                    </div>

                    {{-- Paroolid --}}
                    <div x-data="{ showPassword: false, showConfirm: false }" class="grid gap-5">
                        <div>
                            <label for="password" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                                {{ __('Parool') }}
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
                                    placeholder="{{ __('Loo parool') }}"
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
                                {{ __('Korda parooli') }}
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
                                    placeholder="{{ __('Korda parooli') }}"
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
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-4 text-base font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                    >
                        {{ __('Loo konto') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.auth>