@props([
    'conversation',
    'active' => false,
])

@php
    $user = auth()->user();

    $listing = $conversation->listing;

    $coverImage = $listing
        ? $listing->coverThumbUrlOrPlaceholder()
        : null;

    $otherUser = $user ? $conversation->otherParticipant($user) : null;

    $lastMessage = $conversation->latestMessage;

    $unreadCount = (int) ($conversation->unread_messages_count ?? 0);
    $hasUnread = $unreadCount > 0;

    $cardClasses = $active
        ? 'border-emerald-900/25 bg-emerald-50/80 shadow-sm ring-1 ring-emerald-900/10'
        : ($hasUnread
            ? 'border-emerald-900/20 bg-white shadow-sm hover:border-emerald-900/30 hover:bg-emerald-50/40 hover:shadow-md'
            : 'border-emerald-950/10 bg-white/90 hover:border-emerald-900/20 hover:bg-white hover:shadow-md');

    $titleClasses = $hasUnread
        ? 'text-emerald-950'
        : 'text-zinc-900';

    $previewClasses = $hasUnread
        ? 'font-bold text-emerald-950'
        : 'font-medium text-zinc-600';

    $previewText = null;

    if ($lastMessage) {
        $previewText = \Illuminate\Support\Str::limit((string) $lastMessage->body, 70);

        if ($previewText === '' && $lastMessage->hasAttachments()) {
            $previewText = __('Saatis manuse');
        }

        if ($previewText === '' && $lastMessage->isSystem()) {
            $previewText = __('Süsteemiteade');
        }
    }
@endphp

<a
    href="{{ route('messages.show', $conversation) }}"
    @if($active) aria-current="page" @endif
    class="group block rounded-2xl border p-2.5 transition sm:p-3 {{ $cardClasses }}"
>
    <div class="flex min-w-0 items-start gap-3">
        {{-- Listing image / placeholder --}}
        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-emerald-950/10 sm:h-16 sm:w-16">
            @if($coverImage)
                <img
                    src="{{ $coverImage }}"
                    alt="{{ $listing->title ?? __('Kuulutus') }}"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <div class="flex h-full w-full items-center justify-center px-2 text-center text-[10px] font-semibold leading-tight text-zinc-400">
                    {{ __('Pilt puudub') }}
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="min-w-0 flex-1">
            <div class="flex min-w-0 items-start gap-2">
                <div class="min-w-0 flex-1">
                    <div class="min-w-0 truncate text-sm font-extrabold leading-5 sm:text-[15px] {{ $titleClasses }}">
                        {{ $listing->title ?? __('Kuulutus') }}
                    </div>

                    <div class="mt-0.5 truncate text-xs font-semibold text-zinc-500">
                        {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                    </div>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-1">
                    <div class="whitespace-nowrap pt-0.5 text-[11px] font-semibold text-zinc-400 sm:text-xs">
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

                    @if($hasUnread)
                        <div class="whitespace-nowrap text-[11px] font-extrabold text-red-600 sm:text-xs">
                            {{ __('Lugemata sõnum') }}
                        </div>
                    @endif
                </div>
            </div>

            @if($previewText)
                <div class="mt-1.5 truncate text-sm leading-5 {{ $previewClasses }}">
                    {{ $previewText }}
                </div>
            @else
                <div class="mt-1.5 truncate text-sm font-medium leading-5 text-zinc-400">
                    {{ __('Sõnumeid pole veel') }}
                </div>
            @endif
        </div>
    </div>
</a>