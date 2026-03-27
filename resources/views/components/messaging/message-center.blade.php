@props([
    // Kõik kasutaja nähtavad vestlused vasaku veeru jaoks
    'conversations' => collect(),

    // Hetkel avatud aktiivne vestlus
    'activeConversation' => null,
])

@php
    // Kas aktiivne vestlus on üldse olemas
    $hasActiveConversation = !is_null($activeConversation);

    // Kas oleme vestluste listi vaates või konkreetse vestluse vaates
    $isMessagesIndex = request()->routeIs('messages.index');
    $isMessagesShow = request()->routeIs('messages.show');

    // Mobiilis:
    // - index vaates näitame ainult listi
    // - show vaates näitame ainult vestlust
    $showListOnMobile = $isMessagesIndex;
    $showConversationOnMobile = $isMessagesShow && $hasActiveConversation;

    // Kontrollime, kas praegune kasutaja on aktiivses vestluses müüja
    $isSeller = $hasActiveConversation && auth()->id() === $activeConversation->seller_id;

    // Leiame vestluse teise osapoole
    $otherUser = $hasActiveConversation
        ? ($isSeller ? $activeConversation->buyer : $activeConversation->seller)
        : null;

    // Kuulutus, mille kohta vestlus käib
    $listing = $hasActiveConversation ? $activeConversation->listing : null;

    // Kas selle vestluse puhul tohib veel sõnumeid saata
    $canSendMessages = $hasActiveConversation
        && $listing
        && $listing->status !== 'deleted';

    // Kas mina olen selle teise kasutaja blokeerinud
    $isBlockedByMe = $hasActiveConversation && $otherUser
        ? auth()->user()->hasBlocked($otherUser)
        : false;

    // Kas kasutajate vahel on üldse aktiivne sõnumiblokk ükskõik kummas suunas
    $hasMessagingBlock = $hasActiveConversation
        ? $activeConversation->hasMessagingBlock(auth()->user())
        : false;

    // Route kasutaja blokeerimiseks:
    // ainult siis, kui mina ei ole teda juba blokeerinud
    $blockUserAction = $hasActiveConversation && $otherUser && !$isBlockedByMe
        ? route('user-blocks.store', $otherUser)
        : null;

    // Route blokeeringu eemaldamiseks:
    // ainult siis, kui mina olen selle kasutaja blokeerinud
    $unblockUserAction = $hasActiveConversation && $otherUser && $isBlockedByMe
        ? route('user-blocks.destroy', $otherUser)
        : null;

    // Route vestluse eemaldamiseks kasutaja vaatest
    $hideConversationAction = $hasActiveConversation
        ? route('messages.destroy', $activeConversation)
        : null;

    // Route kasutajast teatamiseks
    $reportUserAction = $hasActiveConversation
        ? route('user-reports.store')
        : null;

    // Aktiivse vestluse id reporti sidumiseks
    $conversationId = $hasActiveConversation
        ? $activeConversation->id
        : null;
@endphp

<div class="mx-auto max-w-7xl px-4 py-6 md:py-8">
    <div class="grid gap-6 lg:h-[calc(100vh-8rem)] lg:grid-cols-[360px_minmax(0,1fr)] lg:min-h-0">

        {{-- Vasak veerg: vestluste list --}}
        <div class="{{ $showListOnMobile ? 'block' : 'hidden' }} lg:block lg:h-full lg:min-h-0">
            <x-messaging.conversation-list
                :conversations="$conversations"
                :active-conversation="$activeConversation"
            />
        </div>

        {{-- Parem veerg: aktiivne vestlus või placeholder --}}
        <section class="{{ $showConversationOnMobile ? 'flex' : 'hidden' }} lg:flex h-[calc(100vh-5rem)] lg:h-full min-h-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            @if($hasActiveConversation)
                {{-- Päis koos mobiili tagasi-nupuga --}}
                <div class="border-b border-zinc-200 px-4 py-4">
                    <div class="mb-4 flex items-center lg:hidden">
                        <x-ui.back-button
                            :href="route('messages.index')"
                            color="emerald"
                        />
                    </div>

                    {{-- Vestluse teise osapoole kaart koos menüü ja blokeerimise olekuga --}}
                    <x-users.profile-summary-card
                        :user="$otherUser"
                        :role-label="$isSeller ? __('Ostja') : __('Müüja')"
                        :score="9.8"
                        :reviews-count="20"
                        :hide-conversation-action="$hideConversationAction"
                        :block-user-action="$blockUserAction"
                        :unblock-user-action="$unblockUserAction"
                        :is-blocked-by-me="$isBlockedByMe"
                        :has-messaging-block="$hasMessagingBlock"
                        :report-user-action="$reportUserAction"
                        :conversation-id="$conversationId"
                    />
                </div>

                {{-- Kuulutuse mini-kaart vestluse kohal --}}
                <div class="border-b border-zinc-200 bg-zinc-50/70 px-4 py-4">
                    <x-listings.mini-card :listing="$listing" />

                    @if($listing && $listing->status === 'deleted')
                        <div class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ __('Sõnumite saatmine on suletud, sest kuulutus on kustutatud.') }}
                        </div>
                    @endif
                </div>

                {{-- Vestluse sõnumid --}}
                <x-messaging.chat-thread :conversation="$activeConversation" />

                {{-- Sisestusala --}}
                @if($canSendMessages)
                    <x-messaging.chat-compose
                        :conversation="$activeConversation"
                        :has-messaging-block="$hasMessagingBlock"
                        :is-blocked-by-me="$isBlockedByMe"
                        :unblock-user-action="$unblockUserAction"
                    />
                @else
                    <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-4">
                        <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-600">
                            {{ __('Selles vestluses ei saa enam uusi sõnumeid saata.') }}
                        </div>
                    </div>
                @endif
            @else
                {{-- Desktop placeholder, kui aktiivset vestlust pole --}}
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