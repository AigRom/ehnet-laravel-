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

    $navLink = 'inline-flex items-center gap-2.5 rounded-2xl px-5 py-3 text-base font-semibold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800';
    $primaryNavLink = 'inline-flex items-center gap-2.5 rounded-2xl bg-emerald-900 px-5 py-3 text-base font-semibold text-white shadow-sm shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-md';
    $mobileLink = 'flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50';
    $mobilePrimaryLink = 'flex items-center gap-2 rounded-xl bg-emerald-900 px-3 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-950/15 transition hover:bg-emerald-800';

    $iconClass = 'h-7 w-7 text-emerald-900';
    $primaryIconClass = 'h-7 w-7 text-white';
    $mobileIconClass = 'h-6 w-6 text-emerald-900';
    $mobilePrimaryIconClass = 'h-6 w-6 text-white';
@endphp

<header
    x-data="{ mobileOpen: false, userOpen: false, mobileUserOpen: false }"
    class="sticky top-0 z-50 w-full border-b border-emerald-950/10 bg-gradient-to-b from-white/95 to-white/85 shadow-sm backdrop-blur-xl"
>
    <div class="mx-auto max-w-[1500px] px-4 sm:px-6 lg:px-8">
        <div class="flex h-24 items-center justify-between gap-8">

            {{-- Logo + Brand --}}
            <a href="{{ route('home') }}"
               class="flex shrink-0 items-center gap-3.5 rounded-xl focus:outline-none focus:ring-0">
                <x-app-logo class="h-14 w-auto" />

                <span class="text-2xl font-extrabold tracking-tight text-emerald-900">
                    EHNET
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex flex-1 items-center justify-end gap-2">
                <a href="{{ route('listings.index') }}" class="{{ $navLink }}">
                    <x-icons.squares-2x2 class="{{ $iconClass }}" />
                    <span>{{ __('Kõik kuulutused') }}</span>
                </a>

                @auth
                    <a href="{{ route('messages.index') }}" class="{{ $navLink }}">
                        <span class="relative">
                            <x-icons.chat-bubble class="{{ $iconClass }}" />

                            @if(($unreadConversationsCount ?? 0) > 0)
                                <span class="absolute -right-2 -top-2 inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white ring-2 ring-white">
                                    {{ $unreadConversationsCount > 99 ? '99+' : $unreadConversationsCount }}
                                </span>
                            @endif
                        </span>

                        <span>{{ __('Sõnumid') }}</span>
                    </a>

                    {{-- Minu EHNET --}}
                    <div class="relative ml-2"
                         @click.outside="userOpen = false"
                         @keydown.escape.window="userOpen = false">

                        <div class="flex items-center overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-sm">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2.5 px-5 py-3 text-base font-semibold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800">
                                <x-icons.user-circle class="{{ $iconClass }}" />
                                <span>{{ __('Minu EHNET') }}</span>
                            </a>

                            <button type="button"
                                    @click.prevent="userOpen = !userOpen"
                                    class="inline-flex items-center justify-center border-l border-emerald-950/10 px-3.5 py-3 text-emerald-800 transition hover:bg-emerald-50 hover:text-emerald-950"
                                    :aria-expanded="userOpen.toString()"
                                    aria-label="{{ __('Ava kasutajamenüü') }}">
                                <svg class="h-5 w-5 transition-transform duration-200"
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
                             class="absolute right-0 mt-3 w-56 overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-xl shadow-emerald-950/10">
                            <div class="px-4 py-3">
                                <div class="text-sm font-semibold text-emerald-950">
                                    {{ auth()->user()->name ?? __('Kasutaja') }}
                                </div>
                                <div class="truncate text-xs text-zinc-500">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>

                            <div class="h-px bg-emerald-950/10"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800">
                                    <x-icons.logout class="{{ $mobileIconClass }}" />
                                    <span>{{ __('Logi välja') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="ml-2 flex items-center gap-2">
                        <a href="{{ $loginUrl }}" class="{{ $navLink }}">
                            <x-icons.login class="{{ $iconClass }}" />
                            <span>{{ __('Logi sisse') }}</span>
                        </a>

                        <a href="{{ $registerUrl }}" class="{{ $navLink }}">
                            <x-icons.user-plus class="{{ $iconClass }}" />
                            <span>{{ __('Registreeru') }}</span>
                        </a>
                    </div>
                @endauth

                {{-- Lisa kuulutus paremal eraldi --}}
                <div class="ml-4">
                    <a href="{{ auth()->check() ? route('listings.create') : $guestCreateListingUrl }}" class="{{ $primaryNavLink }}">
                        <x-icons.plus-circle class="{{ $primaryIconClass }}" />
                        <span>{{ __('Lisa kuulutus') }}</span>
                    </a>
                </div>
            </nav>

            {{-- Mobile button --}}
            <button type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-emerald-950/10 bg-white p-2.5 text-emerald-900 shadow-sm transition hover:bg-emerald-50 md:hidden"
                    @click="mobileOpen = !mobileOpen"
                    aria-label="{{ __('Ava menüü') }}">
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
    <div x-cloak
         x-show="mobileOpen"
         x-transition
         class="border-t border-emerald-950/10 bg-white/95 shadow-lg shadow-emerald-950/5 md:hidden">
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6 lg:px-8">

            <a href="{{ route('listings.index') }}" class="{{ $mobileLink }}">
                <x-icons.squares-2x2 class="{{ $mobileIconClass }}" />
                <span>{{ __('Kõik kuulutused') }}</span>
            </a>

            @auth
                <a href="{{ route('messages.index') }}"
                   class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50">
                    <span class="flex items-center gap-2">
                        <x-icons.chat-bubble class="{{ $mobileIconClass }}" />
                        <span>{{ __('Sõnumid') }}</span>
                    </span>

                    @if(($unreadConversationsCount ?? 0) > 0)
                        <span class="inline-flex min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                            {{ $unreadConversationsCount > 99 ? '99+' : $unreadConversationsCount }}
                        </span>
                    @endif
                </a>

                {{-- Mobile: Minu EHNET --}}
                <div class="mt-3 overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-sm">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}"
                           class="flex flex-1 items-center gap-2 px-3 py-3 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50">
                            <x-icons.user-circle class="{{ $mobileIconClass }}" />
                            <span>{{ __('Minu EHNET') }}</span>
                        </a>

                        <button type="button"
                                @click="mobileUserOpen = !mobileUserOpen"
                                class="inline-flex items-center justify-center border-l border-emerald-950/10 px-3 py-3 text-emerald-800 transition hover:bg-emerald-50 hover:text-emerald-950"
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

                    <div x-cloak x-show="mobileUserOpen" x-transition class="border-t border-emerald-950/10">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center gap-2 px-3 py-3 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50">
                                <x-icons.logout class="{{ $mobileIconClass }}" />
                                <span>{{ __('Logi välja') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mt-3 grid gap-1 border-t border-emerald-950/10 pt-3">
                    <a href="{{ $loginUrl }}" class="{{ $mobileLink }}">
                        <x-icons.login class="{{ $mobileIconClass }}" />
                        <span>{{ __('Logi sisse') }}</span>
                    </a>

                    <a href="{{ $registerUrl }}" class="{{ $mobileLink }}">
                        <x-icons.user-plus class="{{ $mobileIconClass }}" />
                        <span>{{ __('Registreeru') }}</span>
                    </a>
                </div>
            @endauth

            {{-- Lisa kuulutus mobiilis all --}}
            <div class="mt-3 border-t border-emerald-950/10 pt-3">
                <a href="{{ auth()->check() ? route('listings.create') : $guestCreateListingUrl }}" class="{{ $mobilePrimaryLink }}">
                    <x-icons.plus-circle class="{{ $mobilePrimaryIconClass }}" />
                    <span>{{ __('Lisa kuulutus') }}</span>
                </a>
            </div>
        </div>
    </div>
</header>