@props([
    'listings' => null,
])

@php
    $sort = request('sort', 'newest');

    $baseQuery = request()->except('page');

    $sortOptions = [
        'newest' => __('Uusimad enne'),
        'oldest' => __('Vanimad enne'),
        'price_asc' => __('Odavamad enne'),
        'price_desc' => __('Kallimad enne'),
    ];

    $hasActiveFilters = request()->filled('q')
        || request()->filled('category')
        || request()->filled('county');

    $resultCount = $listings && method_exists($listings, 'total')
        ? $listings->total()
        : null;
@endphp

<div class="mt-5 flex flex-col gap-3 rounded-2xl border border-emerald-950/10 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="text-sm font-semibold text-emerald-950">
            @if(!is_null($resultCount))
                {{ __('Leitud :count kuulutust', ['count' => $resultCount]) }}
            @else
                {{ __('Tulemused') }}
            @endif
        </div>

        @if($hasActiveFilters)
            <div class="mt-0.5 text-xs font-medium text-zinc-500">
                {{ __('Kuvatakse filtreeritud tulemusi') }}
            </div>
        @endif
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        @if($hasActiveFilters)
            <a
                href="{{ route('listings.index') }}"
                class="inline-flex h-10 items-center justify-center rounded-xl border border-emerald-950/10 bg-white px-4 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50"
            >
                {{ __('Tühjenda filtrid') }}
            </a>
        @endif

        <form method="GET" action="{{ route('listings.index') }}" class="flex items-center gap-2">
            @foreach(request()->except(['sort', 'page']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $nestedValue)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <label for="sort" class="text-sm font-semibold text-zinc-600">
                {{ __('Sorteeri') }}
            </label>

            <div class="relative">
                <select
                    id="sort"
                    name="sort"
                    onchange="this.form.submit()"
                    class="h-10 appearance-none rounded-xl border border-emerald-950/10 bg-stone-50 px-4 pr-10 text-sm font-semibold text-emerald-950 transition hover:bg-white focus:border-emerald-700/30 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                >
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-emerald-800">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </form>
    </div>
</div>