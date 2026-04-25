@props(['listing'])

@php
    $href = route('listings.show', $listing);
    $img = $listing->coverThumbUrlOrPlaceholder();

    $location = $listing->location?->full_label_et
        ?? $listing->location?->name
        ?? $listing->location?->name_et
        ?? null;

    $shortLocation = $listing->location?->name_et
        ?? $listing->location?->name
        ?? $location;

    $isReserved = $listing->isReserved();
@endphp

<a
    href="{{ $href }}"
    class="group block overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
>
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-zinc-100">
        <img
            src="{{ $img }}"
            alt="{{ $listing->title }}"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03] {{ $isReserved ? 'opacity-60' : '' }}"
            loading="lazy"
            decoding="async"
        />

        @if($isReserved)
            <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                <span class="rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-zinc-900 shadow-sm">
                    {{ __('Broneeritud') }}
                </span>
            </div>
        @endif

        <div class="absolute right-3 top-3">
            <livewire:listings.favorite-toggle :listing="$listing" />
        </div>
    </div>

    <div class="p-3 sm:p-4">
        <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-zinc-900 group-hover:underline sm:text-base">
            {{ $listing->title }}
        </h3>

        @if($location)
            <div class="mt-2 flex items-center gap-1.5 text-xs text-zinc-500 sm:text-sm">
                <svg class="h-3.5 w-3.5 shrink-0 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.5 7-11a7 7 0 0 0-14 0c0 6.5 7 11 7 11Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                </svg>

                <span class="truncate">
                    {{ $shortLocation }}
                </span>
            </div>
        @endif

        <div class="mt-3 flex items-end justify-between gap-3 sm:mt-4">
            <span class="shrink-0 text-[11px] text-zinc-500 sm:text-xs">
                {{ optional($listing->published_at)->format('d.m.Y') ?? '' }}
            </span>

            <div class="min-w-0 text-right">
                <div class="text-[10px] font-bold uppercase tracking-wide text-zinc-400">
                    {{ __('Hind') }}
                </div>

                <div class="truncate text-base font-extrabold leading-tight text-emerald-950 sm:text-lg">
                    {{ $listing->priceLabel() }}
                </div>
            </div>
        </div>
    </div>
</a>