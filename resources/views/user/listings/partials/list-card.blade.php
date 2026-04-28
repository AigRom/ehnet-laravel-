@props([
    'listing',
    'isActive' => false,
])

@php
    $statusHelpText = $listing->statusHelpText();

    $statusHelpClasses = match ($listing->status) {
        'reserved' => 'text-amber-600',
        'sold' => 'text-emerald-700',
        'deleted' => 'text-red-600',
        default => 'text-zinc-500',
    };

    $reviewTrade = $listing->soldTrade;

    $reviewAlreadyLeft = false;

    if (auth()->check() && $reviewTrade) {
        $reviewAlreadyLeft = $reviewTrade->relationLoaded('reviews')
            ? $reviewTrade->reviews->contains('reviewer_id', auth()->id())
            : $reviewTrade->hasReviewFrom(auth()->user());
    }

    $reviewMissing =
        $reviewTrade
        && auth()->check()
        && $reviewTrade->canBeReviewedBy(auth()->user())
        && ! $reviewAlreadyLeft;

    $thumbUrl = $listing->coverThumbUrlOrPlaceholder();
@endphp

<div
    @class([
        'group rounded-2xl border bg-white p-3 transition hover:border-emerald-900/20 hover:shadow-md',
        'border-emerald-900 ring-2 ring-emerald-900/10 shadow-md' => $isActive,
        'border-emerald-950/10' => ! $isActive,
    ])
>
    <div class="flex gap-3 sm:gap-4">
        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-emerald-950/10 sm:h-24 sm:w-24">
            @if($thumbUrl)
                <img
                    src="{{ $thumbUrl }}"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                    alt="{{ $listing->title ?? __('Kuulutus') }}"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <div class="flex h-full w-full items-center justify-center px-2 text-center text-[11px] font-semibold text-zinc-400">
                    {{ __('Pilt puudub') }}
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="truncate text-base font-extrabold text-emerald-950 group-hover:underline">
                        {{ $listing->title ?: __('Pealkiri puudub') }}
                    </div>

                    <div class="mt-1 text-sm font-semibold text-zinc-600">
                        {{ $listing->priceLabel() }}

                        @if($listing->location)
                            <span class="mx-1 text-zinc-300">•</span>
                            {{ $listing->location->name }}
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-1">
                    <x-ui.status-badge
                        :status="$listing->status"
                        :expired="$listing->isExpired()"
                        class="whitespace-nowrap"
                    >
                        {{ $listing->statusLabel() }}
                    </x-ui.status-badge>

                    @if($reviewMissing)
                        <span class="inline-flex items-center whitespace-nowrap rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">
                            {{ __('Jäta tagasiside') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-medium text-zinc-500">
                <span>
                    {{ __('Lisatud') }}:
                    {{ optional($listing->published_at)->format('d.m.Y') ?? '—' }}
                </span>

                @if($listing->showExpiryDate())
                    <span>
                        {{ $listing->expiryLabel() }}
                        {{ $listing->expiryDateText() }}
                    </span>
                @endif

                @if($statusHelpText)
                    <span class="{{ $statusHelpClasses }}">
                        {{ $statusHelpText }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>