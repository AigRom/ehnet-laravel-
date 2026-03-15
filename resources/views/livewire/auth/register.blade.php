@php
    if (request('redirect')) {
        session(['url.intended' => request('redirect')]);
    }
@endphp

<x-layouts.auth>
    <div class="mx-auto w-full max-w-md">
        <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-6 sm:p-8">
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        {{ __('Loo konto') }}
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        {{ __('Sisesta e-post. Saadame kinnituse lingi, millega saad registreerimise lõpule viia.') }}
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if (request('notice') === 'create_listing')
                    <div class="mb-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-100">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 15.75h.007v.008H12v-.008z"
                            />
                        </svg>

                        <div>
                            <div class="font-medium">{{ __('Kuulutuse lisamiseks') }}</div>
                            <div class="opacity-90">{{ __('Palun loo konto või logi sisse.') }}</div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                        <div class="font-medium">{{ __('Palun kontrolli sisestatud andmeid.') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('E-posti aadress') }}
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="email@example.com"
                            oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'E-posti aadress on kohustuslik.' : 'Sisesta korrektne e-posti aadress.')"
                            oninput="this.setCustomValidity('')"
                            class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                        >

                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="inline-flex items-start gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                @checked(old('terms'))
                                class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-900"
                            >

                            <span class="leading-6">
                                {{ __('Nõustun') }}
                                <a
                                    href="{{ route('terms') }}"
                                    wire:navigate
                                    class="font-medium text-emerald-700 underline underline-offset-2 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
                                >
                                    {{ __('kasutustingimustega') }}
                                </a>
                                {{ __('ja') }}
                                <a
                                    href="{{ route('privacy') }}"
                                    wire:navigate
                                    class="font-medium text-emerald-700 underline underline-offset-2 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
                                >
                                    {{ __('privaatsuspoliitikaga') }}
                                </a>
                            </span>
                        </label>

                        @error('terms')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <button
                            type="submit"
                            data-test="register-user-button"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                        >
                            {{ __('Saada kinnituse link') }}
                        </button>

                        <p class="text-center text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                            {{ __('Kinnituse link saadetakse sinu e-postile. Kontrolli ka rämpsposti kausta.') }}
                        </p>
                    </div>
                </form>
            </div>

            <div class="border-t border-zinc-200 bg-zinc-50 px-6 py-4 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-400 sm:px-8">
                <span>{{ __('Sul juba on konto?') }}</span>
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="ml-1 font-medium text-emerald-700 transition hover:text-emerald-800 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300"
                >
                    {{ __('Logi sisse') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.auth>