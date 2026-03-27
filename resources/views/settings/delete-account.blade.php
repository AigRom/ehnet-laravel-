<x-layouts.app.public :title="__('Kustuta konto')">
    <section class="w-full">
        <x-settings.heading />

        <x-settings.layout
            :heading="__('Kustuta konto')"
            :subheading="__('Siin saad oma konto jäädavalt kustutada.')"
        >
            <div
                class="space-y-6"
                x-data="{ openDeleteModal: @js($errors->isNotEmpty()) }"
                @keydown.escape.window="openDeleteModal = false"
            >
                <div class="rounded-3xl border border-red-200 bg-red-50 p-5">
                    <h2 class="text-lg font-semibold text-red-700">
                        {{ __('Püsiv tegevus') }}
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-red-700">
                        {{ __('Konto kustutamine on pöördumatu. Enne kinnitamist veendu, et soovid selle sammu kindlasti teha.') }}
                    </p>
                </div>

                <div>
                    <button
                        type="button"
                        x-on:click="openDeleteModal = true"
                        data-test="delete-user-button"
                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200"
                    >
                        {{ __('Kustuta konto') }}
                    </button>
                </div>

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
                        class="relative z-10 w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl"
                        x-on:click.stop
                    >
                        <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-6">
                            @csrf
                            @method('DELETE')

                            <div class="space-y-2">
                                <h3 class="text-lg font-semibold text-zinc-900">
                                    {{ __('Kas oled kindel, et soovid oma konto kustutada?') }}
                                </h3>

                                <p class="text-sm leading-6 text-zinc-600">
                                    {{ __('Kui konto kustutatakse, eemaldatakse jäädavalt kõik sinu andmed ja seotud ressursid. Kinnitamiseks sisesta oma parool.') }}
                                </p>
                            </div>

                            <div x-data="{ showPassword: false }">
                                <label for="delete_password" class="mb-2 block text-sm font-medium text-zinc-800">
                                    {{ __('Parool') }}
                                </label>

                                <div class="relative">
                                    <input
                                        id="delete_password"
                                        name="password"
                                        type="password"
                                        x-bind:type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 pr-12 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-100"
                                    >

                                    <button
                                        type="button"
                                        x-on:click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-3 inline-flex items-center justify-center text-zinc-500 transition hover:text-zinc-700"
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
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    x-on:click="openDeleteModal = false"
                                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                                >
                                    {{ __('Tühista') }}
                                </button>

                                <button
                                    type="submit"
                                    data-test="confirm-delete-user-button"
                                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200"
                                >
                                    {{ __('Kustuta konto') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </x-settings.layout>
    </section>
</x-layouts.app.public>