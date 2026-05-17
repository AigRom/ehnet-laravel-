@props([
    'listing',
    'reservedTrade' => null,
    'soldTrade' => null,
])

@php
    $user = auth()->user();

    $reservedTrade = $reservedTrade ?? $listing->reservedTrade;
    $soldTrade = $soldTrade ?? $listing->soldTrade;

    $isSeller = $user && (int) $user->id === (int) $listing->user_id;
    $isReservedBuyer = $user && $reservedTrade && (int) $reservedTrade->buyer_id === (int) $user->id;
    $isSoldBuyer = $user && $soldTrade && (int) $soldTrade->buyer_id === (int) $user->id;

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

    $existingConversation = null;

    if ($user && $listing->isReservedForUser($user)) {
        $existingConversation = $listing->conversations()
            ->where('buyer_id', $user->id)
            ->where('seller_id', $listing->user_id)
            ->latest('id')
            ->first();
    }

    $reservedConversationAction = $reservedTrade?->conversation_id
        ? route('messages.show', $reservedTrade->conversation_id)
        : null;

    $sellerReservedConversationAction = $isSeller && $isReserved
        ? $reservedConversationAction
        : null;

    $messageAction = $existingConversation
        ? route('messages.show', $existingConversation)
        : route('listings.conversation.open', $listing);

    $messageLabel = $existingConversation
        ? __('Ava vestlus')
        : __('Saada sõnum');

    $buyIntentAction = route('listings.buy-intent', $listing);

    $currentRelativeUrl = request()->getRequestUri();

    $editAction = route('listings.mine.edit', [
        'listing' => $listing,
        'return_to' => $currentRelativeUrl,
    ]);

    $loginAction = route('login', [
        'redirect' => $currentRelativeUrl,
    ]);

    $primaryButton = 'inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20';

    $darkButton = 'inline-flex w-full items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-zinc-950/15 transition hover:bg-zinc-800 focus:outline-none focus:ring-4 focus:ring-zinc-900/15';

    $secondaryButton = 'inline-flex w-full items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-5 py-3.5 text-sm font-bold text-emerald-950 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10';
@endphp

<div class="space-y-4">
    @if($isDeleted)
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ __('See kuulutus on kustutatud ja ei ole enam avalikult saadaval.') }}
        </div>

    @elseif($isSold)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            @if($isSeller && $soldTrade?->buyer)
                {{ __('See kuulutus on müüdud kasutajale :name.', ['name' => $soldTrade->buyer->name]) }}
            @elseif($isSoldBuyer)
                {{ __('See kuulutus on müüdud sulle.') }}
            @else
                {{ __('See kuulutus on müüdud.') }}
            @endif
        </div>

    @elseif($isReserved)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
            @if($isReservedBuyer)
                {{ __('See kuulutus on broneeritud sulle.') }}
            @elseif($isSeller && $reservedTrade?->buyer)
                {{ __('See kuulutus on broneeritud kasutajale :name.', ['name' => $reservedTrade->buyer->name]) }}
            @else
                {{ __('See kuulutus on hetkel broneeritud.') }}
            @endif
        </div>

    @elseif($isArchived)
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm font-medium text-zinc-600">
            {{ __('See kuulutus on müügist eemaldatud.') }}
        </div>

    @elseif($isExpired)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
            {{ __('See kuulutus on aegunud.') }}
        </div>
    @endif

    <div class="grid gap-3">
        @if($isSeller)
            @if($isPublished)
                <a
                    href="{{ $editAction }}"
                    class="{{ $darkButton }}"
                >
                    {{ __('Muuda kuulutust') }}
                </a>
            @endif

            @if($sellerReservedConversationAction)
                <a
                    href="{{ $sellerReservedConversationAction }}"
                    class="{{ $primaryButton }}"
                >
                    {{ __('Ava vestlus') }}
                </a>

                <p class="text-center text-xs font-medium text-zinc-500">
                    {{ __('Broneeringu tühistamine ja üleantuks märkimine toimub vestluse vaates.') }}
                </p>
            @endif
        @else
            @auth
                @if($canSendMessage)
                    @if($existingConversation)
                        <a
                            href="{{ $messageAction }}"
                            class="{{ $primaryButton }}"
                        >
                            {{ $messageLabel }}
                        </a>
                    @else
                        <form method="POST" action="{{ $messageAction }}">
                            @csrf

                            <button
                                type="submit"
                                class="{{ $primaryButton }}"
                            >
                                {{ $messageLabel }}
                            </button>
                        </form>
                    @endif
                @endif

                @if($canBuyIntent)
                    <form method="POST" action="{{ $buyIntentAction }}">
                        @csrf

                        <button
                            type="submit"
                            class="{{ $secondaryButton }}"
                        >
                            {{ __('Soovin osta') }}
                        </button>
                    </form>
                @endif
            @else
                @if($isPublished)
                    <a
                        href="{{ $loginAction }}"
                        class="{{ $primaryButton }}"
                    >
                        {{ __('Logi sisse, et jätkata') }}
                    </a>
                @endif
            @endauth
        @endif
    </div>
</div>