@props([
    'conversations' => collect(),
    'activeConversation' => null,
])

@php
    $user = auth()->user();

    $hasActiveConversation = !is_null($activeConversation);

    $isMessagesIndex = request()->routeIs('messages.index');
    $isMessagesShow = request()->routeIs('messages.show');

    $showListOnMobile = $isMessagesIndex;
    $showConversationOnMobile = $isMessagesShow && $hasActiveConversation;

    $listing = $hasActiveConversation ? $activeConversation->listing : null;
    $isSeller = $hasActiveConversation && $user && $activeConversation->isSeller($user);

    $otherUser = $hasActiveConversation
        ? ($isSeller ? $activeConversation->buyer : $activeConversation->seller)
        : null;

    $canSendMessages = $hasActiveConversation && $user
        ? $activeConversation->canUserSendMessages($user)
        : false;

    $isBlockedByMe = $hasActiveConversation && $otherUser
        ? $user->hasBlocked($otherUser)
        : false;

    $hasMessagingBlock = $hasActiveConversation && $user
        ? $activeConversation->hasMessagingBlock($user)
        : false;

    $blockUserAction = $hasActiveConversation && $otherUser && ! $isBlockedByMe
        ? route('user-blocks.store', $otherUser)
        : null;

    $unblockUserAction = $hasActiveConversation && $otherUser && $isBlockedByMe
        ? route('user-blocks.destroy', $otherUser)
        : null;

    $hideConversationAction = $hasActiveConversation
        ? route('messages.destroy', $activeConversation)
        : null;

    $reportUserAction = $hasActiveConversation
        ? route('user-reports.store')
        : null;

    $conversationId = $hasActiveConversation ? $activeConversation->id : null;

    $currentTrade = $hasActiveConversation
        ? $activeConversation->currentTrade()
        : null;

    $canShowTradeActions = $hasActiveConversation && $user
        ? $activeConversation->canUserSeeTradeActions($user)
        : false;

    $canExpressInterest = $canShowTradeActions
        && ! $isSeller
        && $listing
        && $listing->canAcceptTradeInterest()
        && ! $activeConversation->hasOpenTrade();

    $canReserve = $canShowTradeActions
        && $isSeller
        && $currentTrade
        && $currentTrade->canBeReserved()
        && $listing?->canAcceptTradeReservation();

    $canMarkAsHandedOver = $canShowTradeActions
        && $isSeller
        && $currentTrade
        && $currentTrade->canBeMarkedAsHandedOver()
        && $listing?->isReserved();

    $canConfirmReceived = $canShowTradeActions
        && ! $isSeller
        && $currentTrade
        && $currentTrade->canBeConfirmedByBuyer();

    $canCancelTrade = $canShowTradeActions
        && $currentTrade
        && $currentTrade->canBeCancelled();

    $showTradeActionBar = $canShowTradeActions
        && ($canExpressInterest || $canReserve || $canMarkAsHandedOver || $canConfirmReceived || $canCancelTrade);

    $canLeaveReview = $hasActiveConversation
        && $user
        && $currentTrade
        && $currentTrade->canBeReviewedBy($user)
        && ! $currentTrade->hasReviewFrom($user);

    $hasLeftReview = $hasActiveConversation
        && $user
        && $currentTrade
        && $currentTrade->hasReviewFrom($user);

    $canSeeContactDetails = $hasActiveConversation
        && $user
        && $otherUser
        && $currentTrade
        && $currentTrade->contactsRevealed()
        && $currentTrade->involvesUser($user);

    $cancelTradeLabel = $currentTrade
        ? match ($currentTrade->status) {
            'interest' => $isSeller
                ? __('Lükka ostusoov tagasi')
                : __('Võta ostusoov tagasi'),
            'reserved' => __('Tühista broneering'),
            'awaiting_confirmation' => __('Katkesta tehing'),
            default => __('Katkesta tehing'),
        }
        : __('Katkesta tehing');
@endphp

<div class="mx-auto max-w-7xl px-3 py-4 md:px-4 md:py-6">
    <div class="grid gap-4 lg:h-[calc(100vh-8rem)] lg:min-h-0 lg:grid-cols-[340px_minmax(0,1fr)] xl:grid-cols-[360px_minmax(0,1fr)]">
        <div class="{{ $showListOnMobile ? 'block' : 'hidden' }} lg:block lg:h-full lg:min-h-0">
            <x-messaging.conversation-list
                :conversations="$conversations"
                :active-conversation="$activeConversation"
            />
        </div>

        <section
            x-data="{ showReviewModal: false }"
            class="{{ $showConversationOnMobile ? 'flex' : 'hidden' }} lg:flex min-h-0 h-[calc(100vh-4.5rem)] flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm sm:h-[calc(100vh-5rem)] lg:h-full"
        >
            @if($hasActiveConversation)
                <div class="border-b border-zinc-200 px-3 py-3 sm:px-4 sm:py-3">
                    <div class="mb-3 flex items-center lg:hidden">
                        <x-ui.back-button
                            :href="route('messages.index')"
                            color="emerald"
                        />
                    </div>

                    <x-users.profile-summary-card
                        :user="$otherUser"
                        :role-label="$isSeller ? __('Ostja') : __('Müüja')"
                        :hide-conversation-action="$hideConversationAction"
                        :block-user-action="$blockUserAction"
                        :unblock-user-action="$unblockUserAction"
                        :is-blocked-by-me="$isBlockedByMe"
                        :has-messaging-block="$hasMessagingBlock"
                        :report-user-action="$reportUserAction"
                        :conversation-id="$conversationId"
                    />

                    @if($canSeeContactDetails)
                        <div class="mt-3">
                            <x-users.contact-card :user="$otherUser" />
                        </div>
                    @endif

                    <div class="mt-3">
                        <x-listings.mini-card
                            :listing="$listing"
                            :href="$hasActiveConversation && $listing ? route('messages.listing.show', $activeConversation) : null"
                            :trade="$currentTrade"
                            :has-left-review="$hasLeftReview"
                        />
                    </div>

                    @if($listing && $listing->isDeletedStatus())
                        <div class="mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 sm:text-sm">
                            {{ __('Sõnumite saatmine ja tehingu jätkamine on suletud, sest kuulutus on kustutatud.') }}
                        </div>
                    @endif

                    @if($showTradeActionBar)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($canExpressInterest)
                                <form method="POST" action="{{ route('messages.interest', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-900 transition hover:bg-zinc-50 sm:text-sm"
                                    >
                                        {{ __('Soovin osta') }}
                                    </button>
                                </form>
                            @endif

                            @if($canReserve)
                                <form method="POST" action="{{ route('messages.reserve', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600 sm:text-sm"
                                    >
                                        {{ __('Broneeri sellele ostjale') }}
                                    </button>
                                </form>
                            @endif

                            @if($canMarkAsHandedOver)
                                <form method="POST" action="{{ route('messages.complete', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 sm:text-sm"
                                    >
                                        {{ __('Märgi üleantuks') }}
                                    </button>
                                </form>
                            @endif

                            @if($canConfirmReceived)
                                <form method="POST" action="{{ route('messages.trades.confirm', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700 sm:text-sm"
                                    >
                                        {{ __('Kinnita kauba kättesaamine') }}
                                    </button>
                                </form>
                            @endif

                            @if($canCancelTrade && $currentTrade)
                                <form method="POST" action="{{ route('messages.trades.cancel', [$activeConversation, $currentTrade]) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50 sm:text-sm"
                                    >
                                        {{ $cancelTradeLabel }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @if($canLeaveReview && $currentTrade)
                        <div class="mt-3">
                            <button
                                type="button"
                                @click="showReviewModal = true"
                                class="inline-flex items-center justify-center rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-700 sm:text-sm"
                            >
                                {{ __('Jäta tagasiside') }}
                            </button>
                        </div>

                        <x-reviews.create-modal
                            :conversation="$activeConversation"
                            :trade="$currentTrade"
                            open-state="showReviewModal"
                        />
                    @endif
                </div>

                <x-messaging.chat-thread :conversation="$activeConversation" />

                @if($canSendMessages)
                    <x-messaging.chat-compose
                        :conversation="$activeConversation"
                        :has-messaging-block="$hasMessagingBlock"
                        :is-blocked-by-me="$isBlockedByMe"
                        :unblock-user-action="$unblockUserAction"
                    />
                @else
                    <div class="border-t border-zinc-200 bg-zinc-50 px-3 py-3 sm:px-4 sm:py-4">
                        <div class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600">
                            {{ __('Selles vestluses ei saa enam uusi sõnumeid saata.') }}
                        </div>
                    </div>
                @endif
            @else
                <div class="flex h-full flex-1 items-center justify-center p-8">
                    <div class="max-w-md text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-500">
                            <x-icons.chat-bubble class="h-8 w-8" />
                        </div>

                        <h2 class="text-lg font-semibold text-zinc-900">
                            {{ __('Vali vestlus') }}
                        </h2>

                        <p class="mt-2 text-sm text-zinc-500">
                            {{ __('Vali vasakult vestlus, et näha sõnumeid ja vastata.') }}
                        </p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>