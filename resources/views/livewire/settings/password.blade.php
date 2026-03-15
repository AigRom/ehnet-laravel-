<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Uuenda hetkel sisse logitud kasutaja parool.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    <x-settings.heading />

    <x-settings.layout
        :heading="__('Muuda parooli')"
        :subheading="__('Kasuta oma konto turvalisuse tagamiseks pikka ja juhuslikku parooli.')"
    >
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6" novalidate>
            @csrf

            <div x-data="{ showCurrent: false }">
                <label for="current_password" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    {{ __('Praegune parool') }}
                </label>

                <div class="relative">
                    <input
                        id="current_password"
                        wire:model="current_password"
                        name="current_password"
                        type="password"
                        x-bind:type="showCurrent ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >

                    <button
                        type="button"
                        x-on:click="showCurrent = !showCurrent"
                        class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        x-bind:aria-label="showCurrent ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                        x-bind:title="showCurrent ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                    >
                        <template x-if="showCurrent">
                            <x-icons.eye class="h-5 w-5" />
                        </template>

                        <template x-if="!showCurrent">
                            <x-icons.eye-off class="h-5 w-5" />
                        </template>
                    </button>
                </div>

                @error('current_password')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showPassword: false }">
                <label for="password" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    {{ __('Uus parool') }}
                </label>

                <div class="relative">
                    <input
                        id="password"
                        wire:model="password"
                        name="password"
                        type="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >

                    <button
                        type="button"
                        x-on:click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        x-bind:aria-label="showPassword ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                        x-bind:title="showPassword ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
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
                    {{ __('Kasuta vähemalt 8 märki ning eelista tugevat ja raskesti äraarvatavat parooli.') }}
                </p>

                @error('password')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showConfirm: false }">
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    {{ __('Korda uut parooli') }}
                </label>

                <div class="relative">
                    <input
                        id="password_confirmation"
                        wire:model="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        x-bind:type="showConfirm ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >

                    <button
                        type="button"
                        x-on:click="showConfirm = !showConfirm"
                        class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        x-bind:aria-label="showConfirm ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                        x-bind:title="showConfirm ? '{{ __('Peida parool') }}' : '{{ __('Näita parooli') }}'"
                    >
                        <template x-if="showConfirm">
                            <x-icons.eye class="h-5 w-5" />
                        </template>

                        <template x-if="!showConfirm">
                            <x-icons.eye-off class="h-5 w-5" />
                        </template>
                    </button>
                </div>

                @error('password_confirmation')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    data-test="update-password-button"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                >
                    {{ __('Salvesta') }}
                </button>

                <x-action-message class="text-sm text-emerald-600 dark:text-emerald-400" on="password-updated">
                    {{ __('Salvestatud.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>