@props([
    'listing' => null,
    'href' => null,
])

@php
    $coverImage = $listing?->coverImageUrl();

    $isExpired = $listing
        && $listing->status === 'published'
        && $listing->expires_at
        && $listing->expires_at->isPast();

    $statusLabel = null;
    $statusClasses = 'bg-zinc-100 text-zinc-700';

    if (!$listing) {
        $statusLabel = __('Kuulutus puudub');
        $link = null;
    } else {
        if ($listing->status === 'deleted') {
            $statusLabel = __('Kustutatud');
            $statusClasses = 'bg-red-100 text-red-700';
        } elseif ($listing->status === 'sold') {
            $statusLabel = __('Müüdud');
            $statusClasses = 'bg-emerald-100 text-emerald-700';
        } elseif ($listing->status === 'archived') {
            $statusLabel = __('Müügist eemaldatud');
            $statusClasses = 'bg-zinc-200 text-zinc-700';
        } elseif ($isExpired) {
            $statusLabel = __('Aegunud');
            $statusClasses = 'bg-amber-100 text-amber-700';
        }

        $canOpen = $listing->status === 'published' && !$isExpired;

        $link = $href ?? ($canOpen ? route('listings.show', $listing) : null);
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
            {{ $listing?->title ?? __('Kuulutus ei ole enam saadaval') }}
        </div>

        <div class="mt-1 text-sm text-zinc-500">
            @if($listing && !is_null($listing->price))
                {{ number_format((float) $listing->price, 2, ',', ' ') }} {{ $listing->currency ?? '€' }}
            @elseif($listing)
                {{ __('Kokkuleppel') }}
            @else
                {{ __('Kuulutus ei ole enam saadaval') }}
            @endif
        </div>

        @if($statusLabel)
            <div class="mt-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                    {{ $statusLabel }}
                </span>
            </div>
        @endif
    </div>
@if($link)
    </a>
@else
    </div>
@endif