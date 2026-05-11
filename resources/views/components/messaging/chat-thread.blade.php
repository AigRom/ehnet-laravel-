@props([
    'conversation',
])

@php
    $lastMessageId = $conversation->messages->last()?->id ?? 0;
@endphp

<div
    x-data="chatScroll()"
    x-init="init()"
    @scroll="onScroll"
    data-chat-thread
    data-chat-poll-url="{{ route('messages.poll', $conversation) }}"
    data-chat-mark-read-url="{{ route('messages.mark-read', $conversation) }}"
    data-csrf-token="{{ csrf_token() }}"
    data-last-message-id="{{ $lastMessageId }}"
    data-polling="0"
    data-marking-read="0"
    class="min-h-0 flex-1 overflow-y-auto bg-gradient-to-b from-stone-50 via-emerald-50/20 to-stone-50 px-3 py-4 sm:px-5 sm:py-6"
>
    <div data-chat-messages class="space-y-5">
        @forelse($conversation->messages as $message)
            <x-messaging.chat-message :message="$message" />
        @empty
            <div data-chat-empty class="flex min-h-[320px] items-center justify-center">
                <div class="rounded-[1.75rem] border border-dashed border-emerald-950/15 bg-white/70 px-6 py-10 text-center shadow-sm backdrop-blur">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-900">
                        <x-icons.chat-bubble class="h-6 w-6" />
                    </div>

                    <div class="text-sm font-semibold text-emerald-950">
                        {{ __('Vestlus on tühi') }}
                    </div>

                    <div class="mt-1 text-sm text-zinc-500">
                        {{ __('Alusta sõnumi saatmisest.') }}
                    </div>
                </div>
            </div>
        @endforelse

        <div x-ref="bottom" data-chat-bottom></div>
    </div>
</div>