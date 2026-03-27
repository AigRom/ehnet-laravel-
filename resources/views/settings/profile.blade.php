<x-layouts.app.public :title="__('Profiil')">
    <section class="w-full">
        <x-settings.heading />

        <x-settings.layout
            :heading="__('Profiil')"
            :subheading="__('Uuenda oma konto põhiandmeid.')"
        >
            <form
                method="POST"
                action="{{ route('profile.update') }}"
                enctype="multipart/form-data"
                class="mt-6 space-y-6"
                novalidate
                x-data="{ preview: null, removeAvatar: false }"
            >
                @csrf
                @method('PATCH')

                {{-- Profiilipilt --}}
                <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                                <template x-if="preview">
                                    <img :src="preview" alt="{{ __('Uus profiilipilt') }}" class="h-full w-full object-cover">
                                </template>

                                <template x-if="!preview && !removeAvatar && @js($user->avatar_url)">
                                    <img src="{{ $user->avatar_url }}" alt="{{ __('Profiilipilt') }}" class="h-full w-full object-cover">
                                </template>

                                <template x-if="!preview && (removeAvatar || !@js($user->avatar_url))">
                                    <div class="flex h-full w-full items-center justify-center">
                                        <span class="text-lg font-semibold text-zinc-600">
                                            {{ $user->initials() }}
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <div>
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ __('Profiilipilt') }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Laadi üles JPG, PNG või WEBP pilt. Maksimaalne suurus 2 MB.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <label
                                for="avatar"
                                class="inline-flex cursor-pointer items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200 dark:hover:bg-zinc-800"
                            >
                                {{ __('Vali pilt') }}
                            </label>

                            <input
                                id="avatar"
                                name="avatar"
                                type="file"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="hidden"
                                @change="
                                    removeAvatar = false;
                                    const file = $event.target.files[0];
                                    preview = file ? URL.createObjectURL(file) : null;
                                "
                            >

                            @if($user->avatar_path)
                                <button
                                    type="button"
                                    @click="
                                        removeAvatar = true;
                                        preview = null;
                                        document.getElementById('avatar').value = '';
                                    "
                                    class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-900/50 dark:bg-zinc-950 dark:text-red-400 dark:hover:bg-red-950/20"
                                >
                                    {{ __('Eemalda pilt') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <input type="hidden" name="remove_avatar" :value="removeAvatar ? 1 : 0">

                    @error('avatar')
                        <p class="mt-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kasutajanimi --}}
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ __('Kasutajanimi') }}
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        minlength="3"
                        maxlength="25"
                        autocomplete="nickname"
                        spellcheck="false"
                        value="{{ old('name', $user->name) }}"
                        placeholder="{{ __('Näiteks: MatiK või Ehnet OÜ') }}"
                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >

                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('See nimi kuvatakse sinu profiilil ja kuulutuste juures. Soovituslik pikkus 3–25 tähemärki.') }}
                    </p>

                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- E-post --}}
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ __('E-post') }}
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        value="{{ old('email', $user->email) }}"
                        placeholder="email@example.com"
                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >

                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Telefoni number --}}
                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ __('Telefoni number') }}
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-zinc-500">
                            +
                        </span>

                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            required
                            autocomplete="tel"
                            inputmode="numeric"
                            pattern="[0-9]{7,15}"
                            minlength="7"
                            maxlength="15"
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="{{ __('37251234567') }}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="block w-full rounded-2xl border border-zinc-300 bg-white py-3 pl-8 pr-4 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                        >
                    </div>

                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Sisesta telefoninumber koos riigikoodiga, ilma + märgita.') }}
                    </p>

                    @error('phone')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Asukoht --}}
                <div class="relative overflow-visible">
                    <livewire:location-autocomplete
                        name="location_id"
                        :selected-id="old('location_id', $user->location_id)"
                    />

                    @error('location_id')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Eraisiku väljad --}}
                @if($user->isCustomer())
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Eesnimi') }}
                            </label>

                            <input
                                id="first_name"
                                name="first_name"
                                type="text"
                                autocomplete="given-name"
                                value="{{ old('first_name', $user->first_name) }}"
                                placeholder="{{ __('Sisesta eesnimi') }}"
                                maxlength="100"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('first_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Perekonnanimi') }}
                            </label>

                            <input
                                id="last_name"
                                name="last_name"
                                type="text"
                                autocomplete="family-name"
                                value="{{ old('last_name', $user->last_name) }}"
                                placeholder="{{ __('Sisesta perekonnanimi') }}"
                                maxlength="100"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('last_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="date_of_birth" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Sünniaeg (valikuline)') }}
                            </label>

                            <input
                                id="date_of_birth"
                                name="date_of_birth"
                                type="date"
                                max="{{ now()->toDateString() }}"
                                value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('date_of_birth')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Ettevõtte väljad --}}
                @if($user->isBusiness())
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="contact_first_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Kontaktisiku eesnimi') }}
                            </label>

                            <input
                                id="contact_first_name"
                                name="contact_first_name"
                                type="text"
                                autocomplete="given-name"
                                value="{{ old('contact_first_name', $user->contact_first_name) }}"
                                placeholder="{{ __('Sisesta kontaktisiku eesnimi') }}"
                                maxlength="100"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('contact_first_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_last_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Kontaktisiku perekonnanimi') }}
                            </label>

                            <input
                                id="contact_last_name"
                                name="contact_last_name"
                                type="text"
                                autocomplete="family-name"
                                value="{{ old('contact_last_name', $user->contact_last_name) }}"
                                placeholder="{{ __('Sisesta kontaktisiku perekonnanimi') }}"
                                maxlength="100"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('contact_last_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Ettevõtte nimi') }}
                            </label>

                            <input
                                id="company_name"
                                name="company_name"
                                type="text"
                                value="{{ old('company_name', $user->company_name) }}"
                                placeholder="{{ __('Sisesta ettevõtte nimi') }}"
                                maxlength="150"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('company_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_reg_no" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                {{ __('Registrikood') }}
                            </label>

                            <input
                                id="company_reg_no"
                                name="company_reg_no"
                                type="text"
                                inputmode="numeric"
                                maxlength="20"
                                value="{{ old('company_reg_no', $user->company_reg_no) }}"
                                placeholder="{{ __('Näiteks 12345678') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                            >

                            @error('company_reg_no')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <button
                        type="submit"
                        data-test="update-profile-button"
                        class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                    >
                        {{ __('Salvesta') }}
                    </button>

                    @if (session('status') === 'profile-updated')
                        <p class="text-sm text-emerald-600 dark:text-emerald-400">
                            {{ __('Salvestatud.') }}
                        </p>
                    @endif
                </div>
            </form>
        </x-settings.layout>


    </section>
</x-layouts.app.public>