@props([
    'conversation',
    'active' => false,
])

@php
    $listing = $conversation->listing;
    $coverImage = $listing?->coverImageUrl();

    $otherUser = $conversation->seller_id === auth()->id()
        ? $conversation->buyer
        : $conversation->seller;

    $lastMessage = $conversation->latestMessage;
    $hasUnread = ($conversation->unread_messages_count ?? 0) > 0;
@endphp

<a
    href="{{ route('messages.show', $conversation) }}"
    class="block rounded-2xl border p-2.5 sm:p-3 transition
        {{ $active
            ? 'border-emerald-500 bg-green-50 shadow-sm dark:border-green-600 dark:bg-blue-950/30'
            : ($hasUnread
                ? 'border-zinc-300 bg-zinc-50 hover:border-green-300 hover:shadow-sm'
                : 'border-zinc-200 bg-white hover:border-green-300 hover:shadow-sm')
        }}"
>
    <div class="flex items-start gap-2.5 sm:gap-3">

        <div class="h-14 w-14 sm:h-16 sm:w-16 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
            @if($coverImage)
                <img
                    src="{{ $coverImage }}"
                    alt="{{ $listing->title }}"
                    class="h-full w-full object-cover"
                >
            @else
                <div class="flex h-full w-full items-center justify-center text-[11px] text-zinc-500">
                    {{ __('Pilt puudub') }}
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2 sm:gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="truncate text-[13px] sm:text-sm font-semibold {{ $hasUnread ? 'text-zinc-950' : 'text-zinc-900' }}">
                            {{ $listing->title ?? __('Kuulutus') }}
                        </div>

                        @if($hasUnread)
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        @endif
                    </div>

                    <div class="truncate text-[11px] sm:text-xs text-zinc-500">
                        {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                    </div>
                </div>

                <div class="shrink-0 text-[11px] sm:text-xs text-zinc-500 whitespace-nowrap">
                    @if($lastMessage && $lastMessage->created_at)
                        @php
                            $sentAt = $lastMessage->created_at;
                        @endphp

                        @if($sentAt->isToday())
                            {{ $sentAt->format('H:i') }}
                        @elseif($sentAt->isYesterday())
                            {{ __('Eile') }}
                        @elseif($sentAt->isCurrentYear())
                            {{ $sentAt->format('d.m') }}
                        @else
                            {{ $sentAt->format('d.m.Y') }}
                        @endif
                    @endif
                </div>
            </div>

            @if($lastMessage)
                <div class="mt-1.5 sm:mt-2 truncate text-[13px] sm:text-sm {{ $hasUnread ? 'font-medium text-zinc-900' : 'text-zinc-600' }}">
                    {{ \Illuminate\Support\Str::limit($lastMessage->body, 70) }}
                </div>
            @endif
        </div>
    </div>
</a>