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
                        {{ __('Unustasid parooli?') }}
                    </h1>

                    <p class="mx-auto mt-3 max-w-lg text-base leading-7 text-zinc-600 dark:text-zinc-400 lg:text-lg">
                        {{ __('Sisesta oma e-posti aadress, et saaksime saata parooli lähtestamise lingi.') }}
                    </p>
                </div>

                {{-- Status --}}
                @if (session('status'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.42-.004l-4-4a1 1 0 1 1 1.414-1.414l3.294 3.293 7.296-7.289a1 1 0 0 1 1.41 0Z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <div>
                            <div class="font-bold">{{ __('Õnnestus') }}</div>
                            <div class="mt-0.5">{{ session('status') }}</div>
                        </div>
                    </div>
                @endif

                {{-- Session error --}}
                @if (session('error'))
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                        </svg>

                        <div>
                            <div class="font-bold">{{ __('Teade') }}</div>
                            <div class="mt-0.5">{{ session('error') }}</div>
                        </div>
                    </div>
                @endif

                {{-- Validation error --}}
                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                        </svg>

                        <div>
                            <div class="font-bold">{{ __('Kontrolli andmeid') }}</div>
                            <div class="mt-0.5">{{ __('Palun kontrolli sisestatud andmeid.') }}</div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-2 block text-base font-bold text-emerald-950 dark:text-zinc-100">
                            {{ __('E-posti aadress') }}
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
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

                    {{-- Submit --}}
                    <button
                        type="submit"
                        data-test="email-password-reset-link-button"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-4 text-base font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                    >
                        {{ __('Saada parooli lähtestamise link') }}
                    </button>
                </form>
            </div>

            <div class="border-t border-emerald-950/10 bg-emerald-50/60 px-7 py-5 text-center text-base text-zinc-700 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-400 sm:px-9 lg:px-12">
                <span>{{ __('Või mine tagasi') }}</span>

                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="ml-1 font-bold text-emerald-900 transition hover:text-emerald-700 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300"
                >
                    {{ __('sisselogimisse') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.auth>