<x-layouts.app.public :title="__('Minu EHNET')">

    <div class="mx-auto w-full max-w-6xl py-6">

        {{-- Pealkiri --}}
        
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                {{ __('Minu EHNET') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Halda oma kuulutusi ja lemmikuid.') }}
            </p>
        </div>

        {{-- Kaardid --}}
        <div class="grid gap-6 sm:grid-cols-2">

            {{-- Minu kuulutused --}}
            <a href="{{ route('listings.mine') }}"
               class="group relative overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition
                      hover:shadow-lg hover:-translate-y-1
                      dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100
                                dark:bg-zinc-800">
                        <x-icons.squares-2x2 class="h-7 w-7 text-zinc-700 dark:text-zinc-200" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Minu kuulutused') }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ __('Vaata ja muuda oma lisatud kuulutusi.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center text-sm font-medium text-zinc-500 group-hover:text-zinc-900
                            dark:text-zinc-400 dark:group-hover:text-white">
                    {{ __('Ava') }}
                    <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-1"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            {{-- Lemmikud --}}
            <a href="{{ route('favorites.index') }}"
               class="group relative overflow-hidden rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition
                      hover:shadow-lg hover:-translate-y-1
                      dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100
                                dark:bg-zinc-800">
                        <x-icons.heart class="h-7 w-7 text-zinc-700 dark:text-zinc-200" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Lemmikud') }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ __('Sinu salvestatud kuulutused.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center text-sm font-medium text-zinc-500 group-hover:text-zinc-900
                            dark:text-zinc-400 dark:group-hover:text-white">
                    {{ __('Ava') }}
                    <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-1"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

        </div>

    </div>

</x-layouts.app.public>