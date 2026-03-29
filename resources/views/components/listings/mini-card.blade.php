@props([
    'listing' => null,
    'href' => null,
])

@php
    $coverImage = $listing?->coverImageUrl();

    if (!$listing) {
        $link = null;
        $title = __('Kuulutus ei ole enam saadaval');
        $price = __('Kuulutus puudub');
        $statusText = __('Kuulutus puudub');
        $expired = false;
        $status = null;
    } else {
        $expired = $listing->isExpired();
        $canOpenPublic = $listing->status === 'published' && !$expired;

        $link = $href ?: ($canOpenPublic ? route('listings.show', $listing) : null);
        $title = $listing->title;
        $price = $listing->priceLabel();
        $status = $listing->status;
        $statusText = in_array($listing->status, ['deleted', 'sold', 'reserved', 'archived', 'published'], true) && $listing->statusLabel() !== 'Aktiivne'
            ? $listing->statusLabel()
            : null;
    }
@endphp

@if($link)
    <a
        href="{{ $link }}"
        class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-3 transition hover:border-green-300 hover:shadow-sm"
    >
@else
    <div
        class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-3 opacity-90"
    >
@endif

    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
        @if($coverImage)
            <img
                src="{{ $coverImage }}"
                alt="{{ $listing?->title ?? __('Kuulutus') }}"
                class="h-full w-full object-cover"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-[11px] text-zinc-500">
                {{ __('Pilt puudub') }}
            </div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <div class="truncate text-sm font-semibold text-zinc-900">
            {{ $title }}
        </div>

        <div class="mt-1 text-sm text-zinc-500">
            {{ $price }}
        </div>

        @if($statusText)
            <div class="mt-2">
                <x-ui.status-badge
                    :status="$status"
                    :expired="$expired"
                >
                    {{ $statusText }}
                </x-ui.status-badge>
            </div>
        @endif
    </div>

@if($link)
    </a>
@else
    </div>
@endif