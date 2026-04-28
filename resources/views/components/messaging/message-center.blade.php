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

    $primaryAction = 'inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-emerald-950/20 transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/20 sm:text-sm';

    $secondaryAction = 'inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-4 py-2.5 text-xs font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10 sm:text-sm';

    $warningAction = 'inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-200 sm:text-sm';

    $dangerAction = 'inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-4 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100 sm:text-sm';

    $reviewAction = 'inline-flex items-center justify-center rounded-2xl bg-violet-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-200 sm:text-sm';
@endphp

<div class="mx-auto w-full max-w-[1500px] px-0 py-0 md:px-4 md:py-6 lg:px-6">
    <div class="grid min-w-0 gap-0 md:gap-4 lg:h-[calc(100vh-8rem)] lg:min-h-0 lg:grid-cols-[380px_minmax(0,1fr)] xl:grid-cols-[420px_minmax(0,1fr)]">

        {{-- Conversation list --}}
        <div class="{{ $showListOnMobile ? 'block' : 'hidden' }} min-w-0 lg:block lg:h-full lg:min-h-0">
            <div class="min-w-0 h-full lg:min-h-0">
                <x-messaging.conversation-list
                    :conversations="$conversations"
                    :active-conversation="$activeConversation"
                />
            </div>
        </div>

        {{-- Conversation --}}
        <section
            x-data="{ showReviewModal: false }"
            class="{{ $showConversationOnMobile ? 'flex' : 'hidden' }} min-w-0 h-[100dvh] min-h-0 flex-col overflow-hidden border-0 bg-white shadow-none md:h-[calc(100dvh-5rem)] md:rounded-[1.75rem] md:border md:border-emerald-950/10 md:shadow-xl md:shadow-emerald-950/5 lg:flex lg:h-full"
        >
            @if($hasActiveConversation)
                <div class="shrink-0 border-b border-emerald-950/10 bg-white px-3 py-3 sm:px-4">
                    <div class="mb-3 flex items-center lg:hidden">
                        <x-ui.back-button
                            :href="route('messages.index')"
                            color="light"
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
                        <div class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700 sm:text-sm">
                            {{ __('Sõnumite saatmine ja tehingu jätkamine on suletud, sest kuulutus on kustutatud.') }}
                        </div>
                    @endif

                    @if($showTradeActionBar)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($canExpressInterest)
                                <form method="POST" action="{{ route('messages.interest', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="{{ $secondaryAction }}">
                                        {{ __('Soovin osta') }}
                                    </button>
                                </form>
                            @endif

                            @if($canReserve)
                                <form method="POST" action="{{ route('messages.reserve', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="{{ $warningAction }}">
                                        {{ __('Broneeri sellele ostjale') }}
                                    </button>
                                </form>
                            @endif

                            @if($canMarkAsHandedOver)
                                <form method="POST" action="{{ route('messages.complete', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="{{ $primaryAction }}">
                                        {{ __('Märgi üleantuks') }}
                                    </button>
                                </form>
                            @endif

                            @if($canConfirmReceived)
                                <form method="POST" action="{{ route('messages.trades.confirm', $activeConversation) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="{{ $primaryAction }}">
                                        {{ __('Kinnita kauba kättesaamine') }}
                                    </button>
                                </form>
                            @endif

                            @if($canCancelTrade && $currentTrade)
                                <form method="POST" action="{{ route('messages.trades.cancel', [$activeConversation, $currentTrade]) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="{{ $dangerAction }}">
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
                                class="{{ $reviewAction }}"
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
                    <div class="shrink-0">
                        <x-messaging.chat-compose
                            :conversation="$activeConversation"
                            :has-messaging-block="$hasMessagingBlock"
                            :is-blocked-by-me="$isBlockedByMe"
                            :unblock-user-action="$unblockUserAction"
                        />
                    </div>
                @else
                    <div class="shrink-0 border-t border-emerald-950/10 bg-stone-50 px-3 py-3 sm:px-4 sm:py-4">
                        <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-600">
                            {{ __('Selles vestluses ei saa enam uusi sõnumeid saata.') }}
                        </div>
                    </div>
                @endif
            @else
                <div class="flex h-full flex-1 items-center justify-center p-8">
                    <div class="max-w-md text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-900">
                            <x-icons.chat-bubble class="h-8 w-8" />
                        </div>

                        <h2 class="text-lg font-bold text-emerald-950">
                            {{ __('Vali vestlus') }}
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-zinc-500">
                            {{ __('Vali vasakult vestlus, et näha sõnumeid ja vastata.') }}
                        </p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>