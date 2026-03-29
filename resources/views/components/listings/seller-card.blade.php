@props([
    'seller',
    'listing',
    'isOwnListing' => false,
    'isAuthenticated' => false,
    'messageAction' => null,
    'buyIntentAction' => null,
    'completeTradeAction' => null,
    'activeListingsCount' => null,
    'score' => null,
    'reviewsCount' => null,
    'profileUrl' => null,
    'reservedTrade' => null,
    'soldTrade' => null,
])

@php
    $joinedYear = optional($seller->created_at)?->format('Y');

    $locationLabel = $seller->location?->full_label_et
        ?? $seller->location?->name
        ?? __('Asukoht lisamata');

    $roleLabel = $seller->company_name ? __('Ettevõte') : __('Eraisik');

    $viewer = auth()->user();

    $isPublished = $listing?->isActivePublished() ?? false;
    $isReserved = $listing?->isReserved() ?? false;
    $isSold = $listing?->isSold() ?? false;

    $reservedBuyer = $reservedTrade?->buyer;
    $soldBuyer = $soldTrade?->buyer;

    $canSendMessage = $listing?->canBuyerSendMessage() ?? false;
    $canBuyIntent = $listing?->canBuyerExpressBuyIntent() ?? false;

    $availabilityMessage = $listing?->sellerCardAvailabilityMessage($viewer);
    $availabilityClasses = $listing?->sellerCardAvailabilityClasses($viewer)
        ?? 'border-zinc-200 bg-zinc-50 text-zinc-600';
@endphp

<div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-zinc-900">
            {{ __('Müüja') }}
        </h2>
    </div>

    @if($isAuthenticated)
        @if($profileUrl)
            <a
                href="{{ $profileUrl }}"
                class="block rounded-2xl transition hover:bg-zinc-50"
            >
                <x-users.profile-card
                    :user="$seller"
                    :role-label="$roleLabel"
                    :joined-year="$joinedYear"
                    :location-label="$locationLabel"
                    :score="$score"
                    :reviews-count="$reviewsCount"
                    class="border-0 bg-transparent p-0 shadow-none"
                />
            </a>
        @else
            <x-users.profile-card
                :user="$seller"
                :role-label="$roleLabel"
                :joined-year="$joinedYear"
                :location-label="$locationLabel"
                :score="$score"
                :reviews-count="$reviewsCount"
                class="border-0 bg-transparent p-0 shadow-none"
            />
        @endif

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-zinc-50 px-3 py-3">
                <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                    {{ __('Aktiivsed kuulutused') }}
                </div>

                <div class="mt-1 text-base font-semibold text-zinc-900">
                    {{ $activeListingsCount ?? '—' }}
                </div>
            </div>

            <div class="rounded-xl bg-zinc-50 px-3 py-3">
                <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                    {{ __('Tagasiside') }}
                </div>

                <div class="mt-1 text-base font-semibold text-zinc-900">
                    @if(!is_null($score))
                        {{ number_format((float) $score, 1, ',', ' ') }}

                        @if(!is_null($reviewsCount))
                            <span class="text-sm font-normal text-zinc-500">
                                ({{ $reviewsCount }})
                            </span>
                        @endif
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4">
            @if($isOwnListing)
                @if($isPublished)
                    <a
                        href="{{ route('listings.mine.edit', $listing) }}"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        {{ __('Muuda kuulutust') }}
                    </a>

                @elseif($isReserved)
                    <div class="space-y-3">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            @if($reservedBuyer)
                                {{ __('Broneeritud kasutajale :name.', ['name' => $reservedBuyer->name]) }}
                            @else
                                {{ __('Kuulutus on broneeritud.') }}
                            @endif
                        </div>

                        @if($completeTradeAction)
                            <form method="POST" action="{{ $completeTradeAction }}">
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                >
                                    {{ __('Märgi müüduks') }}
                                </button>
                            </form>
                        @endif
                    </div>

                @elseif($isSold)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        @if($soldBuyer)
                            {{ __('Müüdud kasutajale :name.', ['name' => $soldBuyer->name]) }}
                        @else
                            {{ __('Kuulutus on müüdud.') }}
                        @endif
                    </div>

                @elseif($availabilityMessage)
                    <div class="rounded-2xl border px-4 py-3 text-sm {{ $availabilityClasses }}">
                        {{ $availabilityMessage }}
                    </div>
                @endif

            @else
                <div class="grid gap-3">
                    @if($messageAction && $canSendMessage)
                        <form method="POST" action="{{ $messageAction }}">
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            >
                                {{ __('Saada sõnum') }}
                            </button>
                        </form>
                    @endif

                    @if($buyIntentAction && $canBuyIntent)
                        <form method="POST" action="{{ $buyIntentAction }}">
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50"
                            >
                                {{ __('Soovin osta') }}
                            </button>
                        </form>
                    @endif

                    @if($availabilityMessage && !$canBuyIntent)
                        <div class="rounded-2xl border px-4 py-3 text-sm {{ $availabilityClasses }}">
                            {{ $availabilityMessage }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full border border-zinc-200 bg-white">
                <x-icons.user-circle class="h-7 w-7 text-zinc-500" />
            </div>

            <p class="mx-auto max-w-sm text-sm leading-6 text-zinc-600">
                {{ __('Logi sisse, et näha profiili ja saata sõnumeid.') }}
            </p>

            <div class="mt-4">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800"
                >
                    {{ __('Logi sisse') }}
                </a>
            </div>
        </div>
    @endif
</div>