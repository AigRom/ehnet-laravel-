@props(['listing'])

@php
    $href = route('listings.show', $listing);
    $img = $listing->coverImageUrl();

    $priceText = 'Kokkuleppel';
    if ($listing->price !== null) {
        $priceText = ((float)$listing->price == 0.0) ? 'Tasuta' : rtrim(rtrim(number_format((float)$listing->price, 2, '.', ''), '0'), '.') . ' ' . ($listing->currency ?? 'EUR');
    }

    $category = $listing->category->name ?? $listing->category->name_et ?? null;

    $location = $listing->location->full_label_et
        ?? $listing->location->name
        ?? $listing->location->name_et
        ?? null;
@endphp

<a href="{{ $href }}"
   class="group block overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition
          hover:-translate-y-0.5 hover:shadow-md
          dark:border-zinc-700 dark:bg-zinc-900">

    {{-- Image --}}
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800">
        @if($img)
            <img
                src="{{ $img }}"
                alt="{{ $listing->title }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                loading="lazy"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-500">
                Pilt puudub
            </div>
        @endif

        {{-- Lemmikud --}}
        <div class="absolute right-3 top-3">
            <livewire:listings.favorite-toggle :listing="$listing" />
        </div>
    </div>

    {{-- Content --}}
    <div class="p-4">
        <h3 class="line-clamp-2 text-base font-semibold text-zinc-900 group-hover:underline dark:text-white">
            {{ $listing->title }}
        </h3>

        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-zinc-600 dark:text-zinc-300">
            @if($location)
                <span>{{ $location }}</span>
            @endif

            @if($location && $category)
                <span class="text-zinc-300 dark:text-zinc-600">•</span>
            @endif

            <!-- @if($category)
                <span>{{ $category }}</span>
            @endif -->
        </div>

        {{-- Alumine rida --}}
        <div class="mt-4 flex items-end justify-between">
            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ optional($listing->published_at)->format('d.m.Y') ?? '' }}
            </span>

            <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                {{ $priceText }}
            </span>
        </div>
    
    </div>
</a>