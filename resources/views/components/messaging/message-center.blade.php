@props([
    'conversations' => collect(),
    'activeConversation' => null,
])

@php
    $hasActiveConversation = !is_null($activeConversation);
    $isMessagesIndex = request()->routeIs('messages.index');
    $isMessagesShow = request()->routeIs('messages.show');

    $showListOnMobile = $isMessagesIndex;
    $showConversationOnMobile = $isMessagesShow && $hasActiveConversation;

    $isSeller = $hasActiveConversation && auth()->id() === $activeConversation->seller_id;
    $otherUser = $hasActiveConversation
        ? ($isSeller ? $activeConversation->buyer : $activeConversation->seller)
        : null;
    $listing = $hasActiveConversation ? $activeConversation->listing : null;
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

        {{-- Parem veerg: aktiivne vestlus / placeholder --}}
        <section class="{{ $showConversationOnMobile ? 'flex' : 'hidden' }} lg:flex h-[calc(100vh-5rem)] lg:h-full min-h-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            @if($hasActiveConversation)
                {{-- Päis + tagasi link mobiilis --}}
                <div class="border-b border-zinc-200 px-4 py-4">
                    <div class="mb-4 flex items-center lg:hidden">

                        <x-ui.back-button
                            :href="route('messages.index')"
                            color="emerald"
                        />

                    </div>

                    <x-users.profile-summary-card
                        :user="$otherUser"
                        :role-label="$isSeller ? __('Ostja') : __('Müüja')"
                        :score="9.8"
                        :reviews-count="20"
                    />
                </div>

                {{-- Kuulutuse kaart --}}
                <div class="border-b border-zinc-200 bg-zinc-50/70 px-4 py-4">
                    <x-listings.mini-card :listing="$listing" />
                </div>

                {{-- Vestlus --}}
                <x-messaging.chat-thread :conversation="$activeConversation" />

                {{-- Sisestusala --}}
                <x-messaging.chat-compose :conversation="$activeConversation" />
            @else
                {{-- Desktop placeholder --}}
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