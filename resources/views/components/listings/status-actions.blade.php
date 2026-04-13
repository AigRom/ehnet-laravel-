@props([
    'listing',
    'reservedTrade' => null,
    'soldTrade' => null,
])

@php
    $user = auth()->user();

    $isSeller = $user && (int) $user->id === (int) $listing->user_id;
    $isReservedBuyer = $user && $reservedTrade && (int) $reservedTrade->buyer_id === (int) $user->id;

    $isExpired = $listing->status === 'published'
        && $listing->expires_at
        && $listing->expires_at->isPast();

    $isPublished = $listing->isActivePublished();
    $isReserved = $listing->isReserved();
    $isSold = $listing->isSold();
    $isDeleted = $listing->isDeletedStatus();
    $isArchived = $listing->status === 'archived';

    $canSendMessage = $listing->canBuyerSendMessage($user);
    $canBuyIntent = $listing->canBuyerExpressBuyIntent();

    $messageAction = route('listings.conversation.open', $listing);
    $buyIntentAction = route('listings.buy-intent', $listing);
    $editAction = route('listings.mine.edit', $listing);
    $completeTradeAction = $reservedTrade ? route('messages.complete', $reservedTrade->conversation_id) : null;
    $cancelTradeAction = $reservedTrade ? route('messages.trades.cancel', [$reservedTrade->conversation_id, $reservedTrade]) : null;
@endphp

<div class="space-y-4">
    @if($isDeleted)
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ __('See kuulutus on kustutatud ja ei ole enam avalikult saadaval.') }}
        </div>

    @elseif($isSold)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            @if($soldTrade?->buyer)
                {{ __('See kuulutus on müüdud kasutajale :name.', ['name' => $soldTrade->buyer->name]) }}
            @else
                {{ __('See kuulutus on müüdud.') }}
            @endif
        </div>

    @elseif($isReserved)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            @if($isReservedBuyer)
                {{ __('See kuulutus on broneeritud sulle.') }}
            @elseif($isSeller && $reservedTrade?->buyer)
                {{ __('See kuulutus on broneeritud kasutajale :name.', ['name' => $reservedTrade->buyer->name]) }}
            @else
                {{ __('See kuulutus on hetkel broneeritud.') }}
            @endif
        </div>

    @elseif($isArchived)
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600">
            {{ __('See kuulutus on müügist eemaldatud.') }}
        </div>

    @elseif($isExpired)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            {{ __('See kuulutus on aegunud.') }}
        </div>
    @endif

    <div class="grid gap-3">
        @if($isSeller)
            @if($isPublished)
                <a
                    href="{{ $editAction }}"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800"
                >
                    {{ __('Muuda kuulutust') }}
                </a>
            @endif

            @if($isReserved && $completeTradeAction && $cancelTradeAction)
                <div class="grid gap-3 sm:grid-cols-2">
                    <form method="POST" action="{{ $completeTradeAction }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        >
                            {{ __('Märgi üleantuks') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ $cancelTradeAction }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700 transition hover:bg-amber-100"
                        >
                            {{ __('Tühista broneering') }}
                        </button>
                    </form>
                </div>
            @endif
        @else
            @auth
                @if($canSendMessage)
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

                @if($canBuyIntent)
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
            @else
                @if($isPublished)
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
                @endif
            @endauth
        @endif
    </div>
</div>