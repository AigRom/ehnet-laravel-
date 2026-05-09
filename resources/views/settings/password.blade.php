<x-layouts.app.public :title="__('Muuda parooli')">
    @php
        $primaryButton = 'inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-800 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-900/20';

        $labelClass = 'mb-2 block text-sm font-bold text-emerald-950';

        $inputClass = 'block w-full rounded-2xl border border-emerald-950/10 bg-white px-4 py-3 pr-12 text-sm font-medium text-emerald-950 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-900/30 focus:ring-4 focus:ring-emerald-900/10';

        $eyeButtonClass = 'absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-emerald-900 focus:outline-none';

        $helpClass = 'mt-2 text-xs font-medium text-zinc-500';

        $errorClass = 'mt-2 text-sm font-semibold text-red-600';
    @endphp

    <div class="mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8">
        <section class="w-full">
            <x-settings.heading />

            <x-settings.layout
                :heading="__('Muuda parooli')"
                :subheading="__('Kasuta oma konto turvalisuse tagamiseks pikka ja juhuslikku parooli.')"
            >
                <form
                    method="POST"
                    action="{{ route('user-password.update') }}"
                    class="mt-6 space-y-6"
                    novalidate
                >
                    @csrf
                    @method('PUT')

                    <div x-data="{ showCurrent: false }">
                        <label for="current_password" class="{{ $labelClass }}">
                            {{ __('Praegune parool') }}
                        </label>

                        <div class="relative">
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                x-bind:type="showCurrent ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="{{ $inputClass }}"
                            >

                            <button
                                type="button"
                                x-on:click="showCurrent = !showCurrent"
                                class="{{ $eyeButtonClass }}"
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
                            <p class="{{ $errorClass }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="password" class="{{ $labelClass }}">
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
                                class="{{ $inputClass }}"
                            >

                            <button
                                type="button"
                                x-on:click="showPassword = !showPassword"
                                class="{{ $eyeButtonClass }}"
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

                        <p class="{{ $helpClass }}">
                            {{ __('Kasuta vähemalt 8 märki ning eelista tugevat ja raskesti äraarvatavat parooli.') }}
                        </p>

                        @error('password')
                            <p class="{{ $errorClass }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ showConfirm: false }">
                        <label for="password_confirmation" class="{{ $labelClass }}">
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
                                class="{{ $inputClass }}"
                            >

                            <button
                                type="button"
                                x-on:click="showConfirm = !showConfirm"
                                class="{{ $eyeButtonClass }}"
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
                            <p class="{{ $errorClass }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button
                            type="submit"
                            data-test="update-password-button"
                            class="{{ $primaryButton }}"
                        >
                            {{ __('Salvesta') }}
                        </button>

                        @if (session('status') === 'password-updated')
                            <p class="w-fit rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-800 ring-1 ring-emerald-900/10">
                                {{ __('Salvestatud.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </x-settings.layout>
        </section>
    </div>
</x-layouts.app.public>