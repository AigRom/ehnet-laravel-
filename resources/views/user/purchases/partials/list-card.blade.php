@props([
    'trade',
    'isActive' => false,
])

@php
    $listing = $trade->listing;
    $seller = $trade->seller;

    $statusLabel = match ($trade->status) {
        'interest' => __('Ostusoov'),
        'reserved' => __('Broneeritud'),
        'awaiting_confirmation' => __('Ootan kinnitust'),
        'completed' => __('Lõpetatud'),
        'cancelled' => __('Katkestatud'),
        default => __('—'),
    };

    $statusClasses = match ($trade->status) {
        'interest' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'reserved' => 'border-amber-200 bg-amber-100 text-amber-800',
        'awaiting_confirmation' => 'border-violet-200 bg-violet-100 text-violet-800',
        'completed' => 'border-emerald-900 bg-emerald-900 text-white',
        'cancelled' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
        default => 'border-zinc-200 bg-zinc-100 text-zinc-700',
    };

    $dateText =
        $trade->buyer_confirmed_received_at?->format('d.m.Y')
        ?? $trade->completed_at?->format('d.m.Y')
        ?? $trade->awaiting_confirmation_at?->format('d.m.Y')
        ?? $trade->reserved_at?->format('d.m.Y')
        ?? $trade->created_at?->format('d.m.Y')
        ?? '—';

    $reviewAlreadyLeft = false;

    if (auth()->check()) {
        $reviewAlreadyLeft = $trade->relationLoaded('reviews')
            ? $trade->reviews->contains('reviewer_id', auth()->id())
            : $trade->hasReviewFrom(auth()->user());
    }

    $reviewMissing =
        auth()->check()
        && $trade->canBeReviewedBy(auth()->user())
        && ! $reviewAlreadyLeft;

    $helpText = match ($trade->status) {
        'reserved' => __('Broneering aktiivne'),
        'awaiting_confirmation' => __('Ootab sinu kinnitust'),
        'completed' => __('Tehing lõpetatud'),
        default => null,
    };

    $helpClasses = match ($trade->status) {
        'reserved' => 'text-amber-600',
        'awaiting_confirmation' => 'text-violet-600',
        'completed' => 'text-emerald-700',
        default => 'text-zinc-500',
    };

    $thumbUrl = $listing?->coverThumbUrlOrPlaceholder() ?? asset('images/placeholder.png');
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
            <img
                src="{{ $thumbUrl }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                alt="{{ $listing?->title ?? __('Kuulutus') }}"
                loading="lazy"
                decoding="async"
            >
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="truncate text-base font-extrabold text-emerald-950 group-hover:underline">
                        {{ $listing?->title ?? __('Kuulutus on eemaldatud') }}
                    </div>

                    <div class="mt-1 text-sm font-semibold text-zinc-600">
                        {{ $listing?->priceLabel() ?? __('—') }}

                        @if($listing?->location)
                            <span class="mx-1 text-zinc-300">•</span>
                            {{ $listing->location->name }}
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-1">
                    <span class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>

                    @if($reviewMissing)
                        <span class="inline-flex items-center whitespace-nowrap rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">
                            {{ __('Jäta tagasiside') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-medium text-zinc-500">
                <span>
                    {{ __('Müüja') }}:
                    {{ $seller?->company_name ?? $seller?->name ?? '—' }}
                </span>

                <span>
                    {{ __('Kuupäev') }}:
                    {{ $dateText }}
                </span>

                @if($helpText)
                    <span class="{{ $helpClasses }}">
                        {{ $helpText }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>