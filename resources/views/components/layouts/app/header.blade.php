@props([
    'title' => null,
])

<header
    x-data="{ mobileOpen: false, userOpen: false }"
    class="sticky top-0 z-50 w-full border-b border-zinc-200/70 bg-white/80 backdrop-blur
           dark:border-zinc-700/70 dark:bg-zinc-900/70"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            {{-- Logo + Brand --}}
            <a href="{{ url('/') }}"
               class="flex items-center gap-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600">
                <x-app-logo />
                <span class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                    EHNET
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ url('/listings') }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900
                          dark:text-zinc-200 dark:hover:bg-zinc-800/60 dark:hover:text-white">
                    {{ __('Kõik kuulutused') }}
                </a>

                @auth
                    <a href="{{ url('/listings/create') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900
                              dark:text-zinc-200 dark:hover:bg-zinc-800/60 dark:hover:text-white">
                        {{ __('Lisa kuulutus') }}
                    </a>

                    <a href="{{ url('/my-listings') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900
                              dark:text-zinc-200 dark:hover:bg-zinc-800/60 dark:hover:text-white">
                        {{ __('Minu kuulutused') }}
                    </a>

                    {{-- User dropdown --}}
                    <div class="relative ml-2" @click.outside="userOpen=false" @keydown.escape.window="userOpen=false">
                        <button type="button"
                                @click="userOpen=!userOpen"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                                       text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900
                                       dark:text-zinc-200 dark:hover:bg-zinc-800/60 dark:hover:text-white"
                                :aria-expanded="userOpen.toString()"
                        >
                            <span class="max-w-[160px] truncate">
                                {{ auth()->user()->name ?? auth()->user()->email }}
                            </span>
                            <svg class="h-4 w-4 text-zinc-500 dark:text-zinc-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-cloak x-show="userOpen" x-transition.origin.top.right
                             class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg
                                    dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="px-4 py-3">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ auth()->user()->name ?? __('Kasutaja') }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>

                            <div class="h-px bg-zinc-200 dark:bg-zinc-800"></div>

                            <a href="{{ url('/settings/profile') }}"
                               class="block px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100
                                      dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                                {{ __('Seaded') }}
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100
                                               dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                                    {{ __('Logi välja') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="ml-2 flex items-center gap-2">
                        <a href="{{ url('/login') }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900
                                  dark:text-zinc-200 dark:hover:bg-zinc-800/60 dark:hover:text-white">
                            {{ __('Logi sisse') }}
                        </a>

                        <a href="{{ url('/register') }}"
                           class="rounded-lg bg-zinc-900 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800
                                  dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            {{ __('Registreeru') }}
                        </a>
                    </div>
                @endauth
            </nav>

            {{-- Mobile button --}}
            <button type="button"
                    class="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-zinc-700 hover:bg-zinc-100
                           dark:text-zinc-200 dark:hover:bg-zinc-800/60"
                    @click="mobileOpen=!mobileOpen"
                    aria-label="{{ __('Ava menüü') }}"
            >
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-cloak x-show="mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile panel --}}
    <div x-cloak x-show="mobileOpen" x-transition class="md:hidden border-t border-zinc-200/70 dark:border-zinc-800/70">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 space-y-1">
            <a href="{{ url('/listings') }}"
               class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100
                      dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                {{ __('Kõik kuulutused') }}
            </a>

            @auth
                <a href="{{ url('/listings/create') }}"
                   class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100
                          dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                    {{ __('Lisa kuulutus') }}
                </a>

                <a href="{{ url('/my-listings') }}"
                   class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100
                          dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                    {{ __('Minu kuulutused') }}
                </a>

                <div class="my-2 h-px bg-zinc-200 dark:bg-zinc-800"></div>

                <a href="{{ url('/settings/profile') }}"
                   class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100
                          dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                    {{ __('Seaded') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-left rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100
                                   dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                        {{ __('Logi välja') }}
                    </button>
                </form>
            @else
                <a href="{{ url('/login') }}"
                   class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100
                          dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                    {{ __('Logi sisse') }}
                </a>

                <a href="{{ url('/register') }}"
                   class="block rounded-lg bg-zinc-900 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800
                          dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                    {{ __('Registreeru') }}
                </a>
            @endauth
        </div>
    </div>
</header>