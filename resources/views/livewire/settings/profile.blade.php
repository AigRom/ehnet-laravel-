<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
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

        $this->email  = $user->email ?? '';

        $this->phone  = $user->phone ?? '';
        $this->location_id = $user->location_id;

        $this->first_name = $user->first_name ?? '';
        $this->last_name  = $user->last_name ?? '';
        $this->date_of_birth = $user->date_of_birth?->format('Y-m-d');

        $this->company_name = $user->company_name ?? '';
        $this->company_reg_no = $user->company_reg_no ?? '';
        $this->contact_first_name = $user->contact_first_name ?? '';
        $this->contact_last_name  = $user->contact_last_name ?? '';
    }

    public function updateProfileInformation(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],

            'phone'       => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],

            'date_of_birth' => ['nullable', 'date'],

            // eraisik
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],

            // ettevõte
            'company_name'       => ['nullable', 'string', 'max:255'],
            'company_reg_no'     => ['nullable', 'string', 'max:50'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name'  => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->isCustomer()) {
            $this->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name'  => ['required', 'string', 'max:255'],
            ]);
        }

        if ($user->isBusiness()) {
            $this->validate([
                'company_name'       => ['required', 'string', 'max:255'],
                'company_reg_no'     => ['required', 'string', 'max:50'],
                'contact_first_name' => ['required', 'string', 'max:255'],
                'contact_last_name'  => ['required', 'string', 'max:255'],
            ]);
        }

        // users.name = UI display name (customer => first_name, business => company_name)
        $validated['name'] = $user->isBusiness()
            ? ($validated['company_name'] ?? '')
            : ($validated['first_name'] ?? '');

        $user->fill($validated);
        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your profile information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            {{-- Email --}}
            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

            {{-- Phone --}}
            <flux:input wire:model="phone" :label="__('Phone number')" type="tel" required autocomplete="tel" />

            {{-- Location (Livewire autocomplete) --}}
            <div class="relative overflow-visible">
                <livewire:location-autocomplete wire:model="location_id" />
                @error('location_id')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Customer fields --}}
            @if(auth()->user()->isCustomer())
                <flux:input wire:model="first_name" :label="__('First name')" type="text" required autocomplete="given-name" />
                <flux:input wire:model="last_name" :label="__('Last name')" type="text" required autocomplete="family-name" />
                <flux:input wire:model="date_of_birth" :label="__('Date of birth (optional)')" type="date" />
            @endif

            {{-- Business fields --}}
            @if(auth()->user()->isBusiness())
                <flux:input wire:model="contact_first_name" :label="__('Contact first name')" type="text" required />
                <flux:input wire:model="contact_last_name" :label="__('Contact last name')" type="text" required />
                <flux:input wire:model="company_name" :label="__('Company name')" type="text" required />
                <flux:input wire:model="company_reg_no" :label="__('Company registration number')" type="text" required />
            @endif

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
