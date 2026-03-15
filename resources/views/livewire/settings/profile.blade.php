<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    // ühised
    public string $phone = '';
    public ?int $location_id = null;

    // eraisik
    public string $first_name = '';
    public string $last_name = '';
    public ?string $date_of_birth = null; // Y-m-d

    // ettevõte
    public string $company_name = '';
    public string $company_reg_no = '';
    public string $contact_first_name = '';
    public string $contact_last_name = '';

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';

        $this->phone = $user->phone ?? '';
        $this->location_id = $user->location_id;

        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->date_of_birth = $user->date_of_birth?->format('Y-m-d');

        $this->company_name = $user->company_name ?? '';
        $this->company_reg_no = $user->company_reg_no ?? '';
        $this->contact_first_name = $user->contact_first_name ?? '';
        $this->contact_last_name = $user->contact_last_name ?? '';
    }

    public function updateProfileInformation(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                Rule::unique(User::class, 'name')->ignore($user->id),
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],

            'phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],

            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],

            // eraisik
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],

            // ettevõte
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_reg_no' => ['nullable', 'string', 'max:50'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->isCustomer()) {
            $this->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
            ]);
        }

        if ($user->isBusiness()) {
            $this->validate([
                'company_name' => ['required', 'string', 'max:255'],
                'company_reg_no' => ['required', 'string', 'max:50'],
                'contact_first_name' => ['required', 'string', 'max:255'],
                'contact_last_name' => ['required', 'string', 'max:255'],
            ]);
        }

        $user->fill($validated);
        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>

<section class="w-full">
    <x-settings.heading />
    <x-settings.layout
        :heading="__('Profiil')"
        :subheading="__('Uuenda oma konto põhiandmeid.')"
    >
        <form wire:submit="updateProfileInformation" class="mt-6 space-y-6" novalidate>
            {{-- Kasutajanimi --}}
            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    {{ __('Kasutajanimi') }}
                </label>

                <input
                    id="name"
                    wire:model="name"
                    name="name"
                    type="text"
                    required
                    minlength="3"
                    maxlength="25"
                    autocomplete="nickname"
                    spellcheck="false"
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
                    wire:model="email"
                    name="email"
                    type="email"
                    required
                    autocomplete="email"
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
                        wire:model="phone"
                        name="phone"
                        type="tel"
                        required
                        autocomplete="tel"
                        inputmode="numeric"
                        pattern="[0-9]{7,15}"
                        minlength="7"
                        maxlength="15"
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
                <livewire:location-autocomplete wire:model="location_id" />

                @error('location_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Eraisiku väljad --}}
            @if(auth()->user()->isCustomer())
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('Eesnimi') }}
                        </label>

                        <input
                            id="first_name"
                            wire:model="first_name"
                            name="first_name"
                            type="text"
                            autocomplete="given-name"
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
                            wire:model="last_name"
                            name="last_name"
                            type="text"
                            autocomplete="family-name"
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
                            wire:model="date_of_birth"
                            name="date_of_birth"
                            type="date"
                            max="{{ now()->toDateString() }}"
                            class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                        >

                        @error('date_of_birth')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            {{-- Ettevõtte väljad --}}
            @if(auth()->user()->isBusiness())
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="contact_first_name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('Kontaktisiku eesnimi') }}
                        </label>

                        <input
                            id="contact_first_name"
                            wire:model="contact_first_name"
                            name="contact_first_name"
                            type="text"
                            autocomplete="given-name"
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
                            wire:model="contact_last_name"
                            name="contact_last_name"
                            type="text"
                            autocomplete="family-name"
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
                            wire:model="company_name"
                            name="company_name"
                            type="text"
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
                            wire:model="company_reg_no"
                            name="company_reg_no"
                            type="text"
                            inputmode="numeric"
                            maxlength="20"
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

                <x-action-message class="text-sm text-emerald-600 dark:text-emerald-400" on="profile-updated">
                    {{ __('Salvestatud.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>