@props([
    'title' => null,
])

<header
    x-data="{ mobileOpen: false, userOpen: false }"
    class="sticky top-0 z-50 w-full border-b border-zinc-200/70 bg-white/80 backdrop-blur"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            {{-- Logo + Brand --}}
            <a href="{{ route('home') }}"
               class="flex items-center gap-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-zinc-400">
                <x-app-logo />
                <span class="text-lg font-semibold tracking-tight text-zinc-900">
                    EHNET
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1">
                {{-- Kuulutused --}}
                <a href="{{ route('listings.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                    <x-icons.squares-2x2 class="w-5 h-5" />
                    <span>{{ __('Kõik kuulutused') }}</span>
                </a>

                {{-- Lisa kuulutus: ALATI nähtav --}}
                <a href="{{ auth()->check()
                            ? route('listings.create')
                            : url('/login?redirect=/listings/create&notice=create_listing') }}"
                   class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                    <x-icons.plus-circle class="w-5 h-5" />
                    <span>{{ __('Lisa kuulutus') }}</span>
                </a>

                @auth
                    {{-- Märguanded (placeholder link) --}}
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                              text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                        <x-icons.bell class="w-5 h-5" />
                        <span>{{ __('Märguanded') }}</span>
                    </a>

                    {{-- Sõnumid --}}
                    <a href="{{ route('messages.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                            text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                        <div class="relative">
                            <x-icons.chat-bubble class="w-5 h-5" />

                            @if(($unreadConversationsCount ?? 0) > 0)
                                <span class="absolute -right-2 -top-2 inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">
                                    {{ $unreadConversationsCount > 99 ? '99+' : $unreadConversationsCount }}
                                </span>
                            @endif
                        </div>

                        <span>{{ __('Sõnumid') }}</span>
                    </a>

                    {{-- User dropdown --}}
                    <div class="relative ml-2"
                         @click.outside="userOpen = false"
                         @keydown.escape.window="userOpen = false">

                        <button type="button"
                                @click="userOpen = !userOpen"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                                       text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900"
                                :aria-expanded="userOpen.toString()">
                            <x-icons.user-circle class="w-5 h-5" />
                            <span class="max-w-[160px] truncate">
                                {{ auth()->user()->name ?? auth()->user()->email }}
                            </span>

                            <svg class="h-4 w-4 text-zinc-500"
                                 viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-cloak
                             x-show="userOpen"
                             x-transition.origin.top.right
                             class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg">
                            <div class="px-4 py-3">
                                <div class="text-sm font-medium text-zinc-900">
                                    {{ auth()->user()->name ?? __('Kasutaja') }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>

                            <div class="h-px bg-zinc-200"></div>

                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200">
                                <x-icons.user-circle class="w-5 h-5" />
                                <span>{{ __('Minu EHNET') }}</span>
                            </a>

                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200">
                                <x-icons.cog-6-tooth class="w-5 h-5" />
                                <span>{{ __('Seaded') }}</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200">
                                    <x-icons.logout class="w-5 h-5" />
                                    <span>{{ __('Logi välja') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="ml-2 flex items-center gap-2">
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                                  text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                            <x-icons.login class="w-5 h-5" />
                            <span>{{ __('Logi sisse') }}</span>
                        </a>

                        <a href="{{ url('/register') }}"
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                                  text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800/60">
                            <x-icons.user-plus class="w-5 h-5" />
                            <span>{{ __('Registreeru') }}</span>
                        </a>
                    </div>
                @endauth
            </nav>

            {{-- Mobile button --}}
            <button type="button"
                    class="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-zinc-700 hover:bg-zinc-100"
                    @click="mobileOpen = !mobileOpen"
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
    <div x-cloak x-show="mobileOpen" x-transition class="md:hidden border-t border-zinc-200/70">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 space-y-1">

            <a href="{{ route('listings.index') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                      text-zinc-700 hover:bg-zinc-100">
                <x-icons.squares-2x2 class="w-5 h-5" />
                <span>{{ __('Kõik kuulutused') }}</span>
            </a>

            <a href="{{ auth()->check()
                        ? route('listings.create')
                        : url('/login?redirect=/listings/create&notice=create_listing') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                      text-zinc-700 hover:bg-zinc-100">
                <x-icons.plus-circle class="w-5 h-5" />
                <span>{{ __('Lisa kuulutus') }}</span>
            </a>

            @auth
                {{-- Märguanded (placeholder link) --}}
                <a href="{{ route('home') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100">
                    <x-icons.bell class="w-5 h-5" />
                    <span>{{ __('Märguanded') }}</span>
                </a>

                <a href="{{ route('messages.index') }}"
                class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium
                        text-zinc-700 hover:bg-zinc-100">
                    <span class="flex items-center gap-2">
                        <x-icons.chat-bubble class="w-5 h-5" />
                        <span>{{ __('Sõnumid') }}</span>
                    </span>

                    @if(($unreadConversationsCount ?? 0) > 0)
                        <span class="inline-flex min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">
                            {{ $unreadConversationsCount > 99 ? '99+' : $unreadConversationsCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100">
                    <x-icons.user-circle class="w-5 h-5" />
                    <span>{{ __('Minu EHNET') }}</span>
                </a>

                <div class="my-2 h-px bg-zinc-200 dark:bg-zinc-800"></div>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100">
                    <x-icons.cog-6-tooth class="w-5 h-5" />
                    <span>{{ __('Seaded') }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                                   text-zinc-700 hover:bg-zinc-100">
                        <x-icons.logout class="w-5 h-5" />
                        <span>{{ __('Logi välja') }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100">
                    <x-icons.login class="w-5 h-5" />
                    <span>{{ __('Logi sisse') }}</span>
                </a>

                <a href="{{ url('/register') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                          text-zinc-700 hover:bg-zinc-100">
                    <x-icons.user-plus class="w-5 h-5" />
                    <span>{{ __('Registreeru') }}</span>
                </a>
            @endauth

        </div>
    </div>
</header>