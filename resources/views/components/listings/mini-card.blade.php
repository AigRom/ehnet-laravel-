@props([
    'listing' => null,
    'href' => null,
    'trade' => null,
    'hasLeftReview' => false,
])

@php
    $coverImage = $listing?->coverThumbUrlOrPlaceholder();

    if (! $listing) {
        $link = null;
        $title = __('Kuulutus ei ole enam saadaval');
        $price = __('Kuulutus puudub');
        $statusText = __('Kuulutus puudub');
        $expired = false;
        $status = null;
    } else {
        $expired = $listing->isExpired();

        $canOpenPublic = in_array($listing->status, ['published', 'reserved'], true)
            && ! $expired;

        $link = $href ?: ($canOpenPublic ? route('listings.show', $listing) : null);
        $title = $listing->title;
        $price = $listing->priceLabel();
        $status = $listing->status;

        $statusText = match (true) {
            $trade && $trade->isAwaitingConfirmation() => __('Ootab kinnitust'),

            in_array($listing->status, ['deleted', 'sold', 'reserved', 'archived', 'published'], true)
                && $listing->statusLabel() !== 'Aktiivne'
                => $listing->statusLabel(),

            default => null,
        };
    }

    $showReceivedBadge = $trade
        && $trade->isCompleted()
        && $trade->isBuyerConfirmed();

    $showReviewedBadge = $trade && $hasLeftReview;

    $locationLabel = $listing?->location?->full_label_et
        ?? $listing?->location?->name;

    $statusBadgeStatus = match (true) {
        $trade && $trade->isAwaitingConfirmation() => 'reserved',
        default => $status,
    };

    $wrapperClasses = 'flex items-center gap-3 rounded-2xl border border-emerald-950/10 bg-white p-2.5 sm:p-3';

    $linkClasses = $wrapperClasses . ' transition hover:border-emerald-900/20 hover:bg-emerald-50/30 hover:shadow-sm';

    $disabledClasses = $wrapperClasses . ' opacity-90';
@endphp

@if($link)
    <a
        href="{{ $link }}"
        class="{{ $linkClasses }}"
    >
@else
    <div class="{{ $disabledClasses }}">
@endif

    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-emerald-950/10 sm:h-16 sm:w-16">
        @if($coverImage)
            <img
                src="{{ $coverImage }}"
                alt="{{ $listing?->title ?? __('Kuulutus') }}"
                class="h-full w-full object-cover transition duration-300 {{ $link ? 'hover:scale-[1.03]' : '' }}"
                loading="lazy"
                decoding="async"
            >
        @else
            <div class="flex h-full w-full items-center justify-center px-2 text-center text-[10px] font-semibold leading-tight text-zinc-400 sm:text-[11px]">
                {{ __('Pilt puudub') }}
            </div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex min-w-0 items-center gap-2">
            <div class="max-w-[55%] flex-none truncate text-sm font-extrabold text-emerald-950 sm:max-w-[60%]">
                {{ $title }}
            </div>

            @if($locationLabel)
                <div class="flex min-w-0 items-center gap-1 text-[11px] font-medium text-zinc-500">
                    <svg class="h-3.5 w-3.5 shrink-0 text-zinc-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9.69 18.933l.31.2.31-.2C14.4 16.36 18 12.28 18 8.5 18 4.91 15.09 2 11.5 2S5 4.91 5 8.5c0 3.78 3.6 7.86 4.69 10.433zM11.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd"/>
                    </svg>

                    <span class="truncate">
                        {{ $locationLabel }}
                    </span>
                </div>
            @endif
        </div>

        <div class="mt-0.5 text-sm font-bold text-emerald-900">
            {{ $price }}
        </div>

        @if($statusText || $showReceivedBadge || $showReviewedBadge)
            <div class="mt-1.5 flex flex-wrap gap-1.5">
                @if($statusText)
                    <x-ui.status-badge
                        :status="$statusBadgeStatus"
                        :expired="$expired"
                    >
                        {{ $statusText }}
                    </x-ui.status-badge>
                @endif

                @if($showReceivedBadge)
                    <x-ui.status-badge status="received">
                        {{ __('Kaup kätte saadud') }}
                    </x-ui.status-badge>
                @endif

                @if($showReviewedBadge)
                    <x-ui.status-badge status="reviewed">
                        {{ __('Tagasiside jäetud') }}
                    </x-ui.status-badge>
                @endif
            </div>
        @endif
    </div>

@if($link)
    </a>
@else
    </div>
@endif