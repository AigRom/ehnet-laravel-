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
    class="block rounded-2xl border p-3 transition
        {{ $active
            ? 'border-blue-500 bg-blue-50 shadow-sm dark:border-blue-600 dark:bg-blue-950/30'
            : ($hasUnread
                ? 'border-zinc-300 bg-zinc-50 hover:border-blue-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900'
                : 'border-zinc-200 bg-white hover:border-blue-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-700')
        }}"
>
    <div class="flex items-start gap-3">

        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
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
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <div class="truncate text-sm font-semibold {{ $hasUnread ? 'text-zinc-950 dark:text-white' : 'text-zinc-900 dark:text-zinc-100' }}">
                            {{ $listing->title ?? __('Kuulutus') }}
                        </div>

                        @if($hasUnread)
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        @endif
                    </div>

                    <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                    </div>
                </div>

                <div class="shrink-0 text-xs text-zinc-500 whitespace-nowrap">
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
                <div class="mt-2 truncate text-sm {{ $hasUnread ? 'font-medium text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 dark:text-zinc-300' }}">
                    {{ \Illuminate\Support\Str::limit($lastMessage->body, 70) }}
                </div>
            @endif
        </div>
    </div>
</a>