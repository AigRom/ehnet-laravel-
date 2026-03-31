@props([
    'conversation',
    'active' => false,
])

@php
    $user = auth()->user();

    $listing = $conversation->listing;
    $coverImage = $listing?->coverImageUrl();

    $otherUser = $user ? $conversation->otherParticipant($user) : null;

    $lastMessage = $conversation->latestMessage;
    $hasUnread = ($conversation->unread_messages_count ?? 0) > 0;

    $cardClasses = $active
        ? 'border-emerald-500 bg-green-50 shadow-sm'
        : ($hasUnread
            ? 'border-zinc-300 bg-zinc-50 hover:border-green-300 hover:shadow-sm'
            : 'border-zinc-200 bg-white hover:border-green-300 hover:shadow-sm');

    $titleClasses = $hasUnread ? 'text-zinc-950' : 'text-zinc-900';
    $previewClasses = $hasUnread ? 'font-medium text-zinc-900' : 'text-zinc-600';

    $previewText = null;

    if ($lastMessage) {
        $previewText = \Illuminate\Support\Str::limit((string) $lastMessage->body, 60);

        if ($previewText === '' && $lastMessage->hasAttachments()) {
            $previewText = __('Saatis manuse');
        }
    }
@endphp

<a
    href="{{ route('messages.show', $conversation) }}"
    class="block rounded-xl border p-2 transition sm:rounded-2xl sm:p-3 {{ $cardClasses }}"
>
    <div class="flex items-start gap-2 sm:gap-3">
        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-zinc-100 sm:h-16 sm:w-16 sm:rounded-xl">
            @if($coverImage)
                <img
                    src="{{ $coverImage }}"
                    alt="{{ $listing->title ?? __('Kuulutus') }}"
                    class="h-full w-full object-cover"
                >
            @else
                <div class="flex h-full w-full items-center justify-center px-1 text-[10px] text-zinc-500">
                    {{ __('Pilt puudub') }}
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start gap-2">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        <div class="truncate text-[12px] font-semibold sm:text-sm {{ $titleClasses }}">
                            {{ $listing->title ?? __('Kuulutus') }}
                        </div>

                        @if($hasUnread)
                            <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-red-500"></span>
                        @endif
                    </div>

                    <div class="mt-0.5 truncate text-[11px] text-zinc-500 sm:text-xs">
                        {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                    </div>
                </div>

                <div class="shrink-0 whitespace-nowrap pl-1 text-[10px] text-zinc-500 sm:text-xs">
                    @if($lastMessage?->created_at)
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

            @if($previewText)
                <div class="mt-1 truncate text-[12px] sm:mt-2 sm:text-sm {{ $previewClasses }}">
                    {{ $previewText }}
                </div>
            @endif
        </div>
    </div>
</a>