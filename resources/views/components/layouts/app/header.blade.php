@props([
    'title' => null,
])

@php
    $authRedirect = request()->fullUrl();

    $guestCreateListingUrl = route('login', [
        'redirect' => route('listings.create'),
        'notice' => 'create_listing',
    ]);

    $loginUrl = route('login', ['redirect' => $authRedirect]);
    $registerUrl = route('register', ['redirect' => $authRedirect]);
@endphp

<header
    x-data="{ mobileOpen: false, userOpen: false, mobileUserOpen: false }"
    class="sticky top-0 z-50 w-full border-b border-zinc-200/70 bg-white/80 backdrop-blur"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            {{-- Logo + Brand --}}
            <a href="{{ route('home') }}"
               class="flex items-center gap-2 rounded-lg focus:outline-none focus:ring-0">
                <x-app-logo class="h-10 w-auto" />
                <!-- <span class="text-lg font-semibold tracking-tight text-zinc-900">
                    EHNET
                </span> -->
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1">
                {{-- Kõik kuulutused --}}
                <a href="{{ route('listings.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                    <x-icons.squares-2x2 class="h-5 w-5" />
                    <span>{{ __('Kõik kuulutused') }}</span>
                </a>

                {{-- Lisa kuulutus --}}
                <a href="{{ auth()->check() ? route('listings.create') : $guestCreateListingUrl }}"
                   class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                    <x-icons.plus-circle class="h-5 w-5" />
                    <span>{{ __('Lisa kuulutus') }}</span>
                </a>

                @auth
                    {{-- Märguanded --}}
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                        <x-icons.bell class="h-5 w-5" />
                        <span>{{ __('Märguanded') }}</span>
                    </a>

                    {{-- Sõnumid --}}
                    <a href="{{ route('messages.index') }}"
                       class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                        <div class="relative">
                            <x-icons.chat-bubble class="h-5 w-5" />

                            @if(($unreadConversationsCount ?? 0) > 0)
                                <span class="absolute -right-2 -top-2 inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">
                                    {{ $unreadConversationsCount > 99 ? '99+' : $unreadConversationsCount }}
                                </span>
                            @endif
                        </div>

                        <span>{{ __('Sõnumid') }}</span>
                    </a>

                    {{-- Minu EHNET + Logi välja --}}
                    <div class="relative ml-2"
                         @click.outside="userOpen = false"
                         @keydown.escape.window="userOpen = false">

                        <div class="flex items-center overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
                            {{-- Dashboard link --}}
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                                <x-icons.user-circle class="h-5 w-5" />
                                <span>{{ __('Minu EHNET') }}</span>
                            </a>

                            {{-- Dropdown toggle --}}
                            <button type="button"
                                    @click.prevent="userOpen = !userOpen"
                                    class="inline-flex items-center justify-center border-l border-zinc-200 px-2.5 py-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700"
                                    :aria-expanded="userOpen.toString()"
                                    aria-label="{{ __('Ava kasutajamenüü') }}">
                                <svg class="h-4 w-4 transition-transform duration-200"
                                     :class="{ 'rotate-180': userOpen }"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path fill-rule="evenodd"
                                          d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>

                        <div x-cloak
                             x-show="userOpen"
                             x-transition.origin.top.right
                             class="absolute right-0 mt-2 w-52 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg">
                            <div class="px-4 py-3">
                                <div class="text-sm font-medium text-zinc-900">
                                    {{ auth()->user()->name ?? __('Kasutaja') }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>

                            <div class="h-px bg-zinc-200"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                                    <x-icons.logout class="h-5 w-5" />
                                    <span>{{ __('Logi välja') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="ml-2 flex items-center gap-2">
                        <a href="{{ $loginUrl }}"
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                            <x-icons.login class="h-5 w-5" />
                            <span>{{ __('Logi sisse') }}</span>
                        </a>

                        <a href="{{ $registerUrl }}"
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900">
                            <x-icons.user-plus class="h-5 w-5" />
                            <span>{{ __('Registreeru') }}</span>
                        </a>
                    </div>
                @endauth
            </nav>

            {{-- Mobile button --}}
            <button type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-700 hover:bg-zinc-100 md:hidden"
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
    <div x-cloak x-show="mobileOpen" x-transition class="border-t border-zinc-200/70 md:hidden">
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6 lg:px-8">

            <a href="{{ route('listings.index') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                <x-icons.squares-2x2 class="h-5 w-5" />
                <span>{{ __('Kõik kuulutused') }}</span>
            </a>

            <a href="{{ auth()->check() ? route('listings.create') : $guestCreateListingUrl }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                <x-icons.plus-circle class="h-5 w-5" />
                <span>{{ __('Lisa kuulutus') }}</span>
            </a>

            @auth
                <a href="{{ route('home') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                    <x-icons.bell class="h-5 w-5" />
                    <span>{{ __('Märguanded') }}</span>
                </a>

                <a href="{{ route('messages.index') }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                    <span class="flex items-center gap-2">
                        <x-icons.chat-bubble class="h-5 w-5" />
                        <span>{{ __('Sõnumid') }}</span>
                    </span>

                    @if(($unreadConversationsCount ?? 0) > 0)
                        <span class="inline-flex min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">
                            {{ $unreadConversationsCount > 99 ? '99+' : $unreadConversationsCount }}
                        </span>
                    @endif
                </a>

                {{-- Mobile: Minu EHNET + Logi välja --}}
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}"
                           class="flex flex-1 items-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                            <x-icons.user-circle class="h-5 w-5" />
                            <span>{{ __('Minu EHNET') }}</span>
                        </a>

                        <button type="button"
                                @click="mobileUserOpen = !mobileUserOpen"
                                class="inline-flex items-center justify-center border-l border-zinc-200 px-3 py-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700"
                                :aria-expanded="mobileUserOpen.toString()"
                                aria-label="{{ __('Ava kasutajamenüü') }}">
                            <svg class="h-4 w-4 transition-transform duration-200"
                                 :class="{ 'rotate-180': mobileUserOpen }"
                                 viewBox="0 0 20 20"
                                 fill="currentColor"
                                 aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div x-cloak x-show="mobileUserOpen" x-transition class="border-t border-zinc-200">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                                <x-icons.logout class="h-5 w-5" />
                                <span>{{ __('Logi välja') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ $loginUrl }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                    <x-icons.login class="h-5 w-5" />
                    <span>{{ __('Logi sisse') }}</span>
                </a>

                <a href="{{ $registerUrl }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                    <x-icons.user-plus class="h-5 w-5" />
                    <span>{{ __('Registreeru') }}</span>
                </a>
            @endauth

        </div>
    </div>
</header>