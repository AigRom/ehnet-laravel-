<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Kustuta hetkel sisse logitud kasutaja.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section
    class="mt-10 space-y-6"
    x-data="{ openDeleteModal: @js($errors->isNotEmpty()) }"
    @keydown.escape.window="openDeleteModal = false"
>
    <div class="relative mb-5">
        <h2 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">
            {{ __('Kustuta konto') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Kustuta oma konto ja kõik sellega seotud andmed.') }}
        </p>
    </div>

    <button
        type="button"
        x-on:click="openDeleteModal = true"
        data-test="delete-user-button"
        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 dark:focus:ring-red-900/40"
    >
        {{ __('Kustuta konto') }}
    </button>

    {{-- Modal --}}
    <div
        x-show="openDeleteModal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        aria-modal="true"
        role="dialog"
    >
        <div
            class="absolute inset-0"
            x-on:click="openDeleteModal = false"
        ></div>

        <div
            class="relative z-10 w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            x-on:click.stop
        >
            <form method="POST" wire:submit="deleteUser" class="space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ __('Kas oled kindel, et soovid oma konto kustutada?') }}
                    </h3>

                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        {{ __('Kui konto kustutatakse, eemaldatakse jäädavalt kõik sinu andmed ja seotud ressursid. Kinnitamiseks sisesta oma parool.') }}
                    </p>
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="delete_password" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ __('Parool') }}
                    </label>

                    <div class="relative">
                        <input
                            id="delete_password"
                            wire:model="password"
                            name="password"
                            type="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-red-500 dark:focus:ring-red-900/30"
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

                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        x-on:click="openDeleteModal = false"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        {{ __('Tühista') }}
                    </button>

                    <button
                        type="submit"
                        data-test="confirm-delete-user-button"
                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 dark:focus:ring-red-900/40"
                    >
                        {{ __('Kustuta konto') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>