@props(['listing'])

@php
    $href = route('listings.show', $listing);
    $img = $listing->coverImageUrl();

    $location = $listing->location?->full_label_et
        ?? $listing->location?->name
        ?? $listing->location?->name_et
        ?? null;
@endphp

<a
    href="{{ $href }}"
    class="group block overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
>
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-zinc-100">
        @if($img)
            <img
                src="{{ $img }}"
                alt="{{ $listing->title }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                loading="lazy"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-500">
                {{ __('Pilt puudub') }}
            </div>
        @endif

        <div class="absolute right-3 top-3">
            <livewire:listings.favorite-toggle :listing="$listing" />
        </div>
    </div>

    <div class="p-4">
        <h3 class="line-clamp-2 text-base font-semibold text-zinc-900 group-hover:underline">
            {{ $listing->title }}
        </h3>

        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-zinc-600">
            @if($location)
                <span>{{ $location }}</span>
            @endif
        </div>

        <div class="mt-4 flex items-end justify-between">
            <span class="text-xs text-zinc-500">
                {{ optional($listing->published_at)->format('d.m.Y') ?? '' }}
            </span>

            <span class="text-sm font-semibold text-zinc-900">
                {{ $listing->priceLabel() }}
            </span>
        </div>
    </div>
</a>