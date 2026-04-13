<x-layouts.app.public :title="__('Minu EHNET')">
    @php
        $user = auth()->user();

        $displayName =
            $user->company_name
            ?? $user->name
            ?? $user->email
            ?? __('Kasutaja');

        $memberSince = optional($user->created_at)?->format('d.m.Y');

        $locationLabel =
            $user->location?->full_label_et
            ?? $user->location?->name
            ?? __('Asukoht lisamata');

        $avatarUrl = $user->avatar_url ?? null;

        $activeListingsCount = $activeListingsCount ?? 0;
        $favoritesCount = $favoritesCount ?? 0;
    @endphp

    <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm">
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-50 via-white to-zinc-100"></div>

            <div class="relative flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="mb-3 inline-flex items-center rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-600 shadow-sm">
                        {{ __('Minu EHNET') }}
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 sm:text-3xl">
                        {{ __('Tere tulemast tagasi, :name', ['name' => $user->name ?? __('kasutaja')]) }}
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-600 sm:text-base">
                        {{ __('Halda oma kuulutusi, lemmikuid ja kontoandmeid ühest kohast.') }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('listings.create') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                            <x-icons.plus-circle class="h-5 w-5" />
                            <span>{{ __('Lisa kuulutus') }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:w-[360px]">
                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                            {{ __('Aktiivsed kuulutused') }}
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-zinc-900">
                            {{ $activeListingsCount }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                            {{ __('Lemmikud') }}
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-zinc-900">
                            {{ $favoritesCount }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-8 grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <section class="overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-100 px-5 py-4 sm:px-6 sm:py-5">
                        <h2 class="text-lg font-semibold text-zinc-900">
                            {{ __('Profiil') }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            {{ __('Sinu konto lühikokkuvõte.') }}
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}"
                                         alt="{{ $displayName }}"
                                         class="h-full w-full object-cover">
                                @else
                                    <x-icons.user-circle class="h-10 w-10 text-zinc-400" />
                                @endif
                            </div>

                            <div class="min-w-0 w-full">
                                <div class="break-words text-lg font-semibold text-zinc-900">
                                    {{ $displayName }}
                                </div>

                                <div class="mt-1 break-all text-sm text-zinc-500">
                                    {{ $user->email }}
                                </div>

                                <div class="mt-2 inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">
                                    {{ $user->company_name ? __('Ettevõte') : __('Eraisik') }}
                                </div>
                            </div>
                        </div>

                        <dl class="mt-6 space-y-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <dt class="text-sm text-zinc-500">{{ __('Asukoht') }}</dt>
                                <dd class="text-sm font-medium text-zinc-900 sm:text-right">
                                    {{ $locationLabel }}
                                </dd>
                            </div>

                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <dt class="text-sm text-zinc-500">{{ __('Liitunud') }}</dt>
                                <dd class="text-sm font-medium text-zinc-900 sm:text-right">
                                    {{ $memberSince ?? '—' }}
                                </dd>
                            </div>
                        </dl>

                        @php
                            $reviewsCountValue = $user->reviewsCount();
                            $scoreValue = $user->averageRating();
                            $hasReviews = $user->hasReviews();
                        @endphp

                        <div class="mt-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <div class="text-sm text-zinc-500">
                                {{ __('Tagasiside') }}
                            </div>

                            @if($hasReviews)
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div class="text-lg font-semibold text-zinc-900">
                                        ⭐ {{ number_format($scoreValue, 1, ',', ' ') }}
                                    </div>

                                    <div class="text-sm text-zinc-500 text-right">
                                        {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCountValue, ['count' => $reviewsCountValue]) }}
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 text-sm text-zinc-500">
                                    {{ __('Tagasiside puudub') }}
                                </div>
                            @endif
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('profile.edit') }}"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-900">
                                <x-icons.cog-6-tooth class="h-5 w-5" />
                                <span>{{ __('Muuda profiili ja seadeid') }}</span>
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-8">
                <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2">
                    <a href="{{ route('listings.mine') }}"
                       class="group relative overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-2xl bg-zinc-100">
                                <x-icons.squares-2x2 class="h-6 w-6 sm:h-7 sm:w-7 text-zinc-700" />
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-base sm:text-lg font-semibold text-zinc-900">
                                    {{ __('Minu kuulutused') }}
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-zinc-600">
                                    {{ __('Vaata, muuda ja halda oma lisatud kuulutusi ühest kohast.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-500 group-hover:text-zinc-900">
                                {{ __('Ava') }}
                            </span>

                            <svg class="h-5 w-5 text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-700"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('favorites.index') }}"
                       class="group relative overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-2xl bg-zinc-100">
                                <x-icons.heart class="h-6 w-6 sm:h-7 sm:w-7 text-zinc-700" />
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-base sm:text-lg font-semibold text-zinc-900">
                                    {{ __('Lemmikud') }}
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-zinc-600">
                                    {{ __('Sinu salvestatud kuulutused, et saaksid neile kiiresti tagasi tulla.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-500 group-hover:text-zinc-900">
                                {{ __('Ava') }}
                            </span>

                            <svg class="h-5 w-5 text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-700"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('messages.index') }}"
                       class="group relative overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-2xl bg-zinc-100">
                                <x-icons.chat-bubble class="h-6 w-6 sm:h-7 sm:w-7 text-zinc-700" />
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-base sm:text-lg font-semibold text-zinc-900">
                                    {{ __('Sõnumid') }}
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-zinc-600">
                                    {{ __('Halda vestlusi ostjate ja müüjatega ning hoia suhtlus ühes kohas.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-500 group-hover:text-zinc-900">
                                {{ __('Ava') }}
                            </span>

                            <svg class="h-5 w-5 text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-700"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('purchases.index') }}"
                       class="group relative overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] border border-zinc-200 bg-white p-5 sm:p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-2xl bg-zinc-100">
                                <x-icons.shopping-bag class="h-6 w-6 sm:h-7 sm:w-7 text-zinc-700" />
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-base sm:text-lg font-semibold text-zinc-900">
                                    {{ __('Minu ostud') }}
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-zinc-600">
                                    {{ __('Vaata oma ostusoove, broneeringuid ja lõpetatud tehinguid.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-500 group-hover:text-zinc-900">
                                {{ __('Ava') }}
                            </span>

                            <svg class="h-5 w-5 text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-700"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app.public>