@props([
    'seller',
    'listing',
    'isOwnListing' => false,
    'messageAction' => null,
    'buyIntentAction' => null,
    'completeTradeAction' => null,
    'activeListingsCount' => null,
    'profileUrl' => null,
    'reservedTrade' => null,
    'soldTrade' => null,
])

@php
    $joinedYear = optional($seller->created_at)?->format('Y');

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

    @if($profileUrl)
        <a
            href="{{ $profileUrl }}"
            class="block rounded-2xl transition hover:bg-zinc-50"
        >
            <x-users.profile-card
                :user="$seller"
                :role-label="$roleLabel"
                :joined-year="$joinedYear"
                :location-label="null"
                class="border-0 bg-transparent p-0 shadow-none"
            />
        </a>
    @else
        <x-users.profile-card
            :user="$seller"
            :role-label="$roleLabel"
            :joined-year="$joinedYear"
            :location-label="null"
            class="border-0 bg-transparent p-0 shadow-none"
        />
    @endif

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
                @auth
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

                    @if($availabilityMessage && ! $canBuyIntent)
                        <div class="rounded-2xl border px-4 py-3 text-sm {{ $availabilityClasses }}">
                            {{ $availabilityMessage }}
                        </div>
                    @endif
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        {{ __('Logi sisse, et saata sõnum') }}
                    </a>

                    <a
                        href="{{ route('login') }}"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50"
                    >
                        {{ __('Logi sisse, et osta') }}
                    </a>
                @endauth
            </div>
        @endif
    </div>
</div>