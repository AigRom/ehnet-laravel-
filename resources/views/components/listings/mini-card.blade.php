@props([
    'listing',
    'href' => null,
])

@php
    $coverImage = $listing?->coverImageUrl();
    $link = $href ?? route('listings.show', $listing);
@endphp

<a
    href="{{ $link }}"
    class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-3 transition hover:border-blue-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
>
    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
        @if($coverImage)
            <img
                src="{{ $coverImage }}"
                alt="{{ $listing->title }}"
                class="h-full w-full object-cover"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-[11px] text-zinc-500">
                {{ __('Pilt puudub') }}
            </div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <div class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $listing->title }}
        </div>

        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            @if(!is_null($listing->price))
                {{ number_format((float) $listing->price, 2, ',', ' ') }} {{ $listing->currency ?? '€' }}
            @else
                {{ __('Kokkuleppel') }}
            @endif
        </div>
    </div>
</a>