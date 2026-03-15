@props([
    'action' => url('/listings'),
    'categories' => collect(),
    'showSort' => true,
    'showReset' => true,
    'variant' => 'panel', // panel | inline
])

@php
    $q = request('q');
    $category = request('category');
    $sort = request('sort', 'newest');
@endphp

<form method="GET" action="{{ $action }}"
      class="{{ $variant === 'panel'
            ? 'mt-6 md:sticky md:top-20 z-40 rounded-3xl border border-zinc-200/70 bg-white/80 p-5 shadow-lg backdrop-blur dark:border-zinc-700/70 dark:bg-zinc-900/70'
            : 'mt-6' }}">

    <div class="grid gap-4 md:grid-cols-12 md:items-center">

        {{-- SEARCH --}}
        <div class="{{ $showSort ? 'md:col-span-5' : 'md:col-span-7' }}">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                {{ __('Otsi') }}
            </label>

            <div class="mt-1 flex items-center rounded-2xl border border-zinc-200 bg-white shadow-sm transition focus-within:ring-2 focus-within:ring-zinc-300 dark:border-zinc-700 dark:bg-zinc-950 dark:focus-within:ring-zinc-800">
                <div class="pl-4 text-zinc-400 dark:text-zinc-500">
                    <x-icons.magnifying-glass class="h-5 w-5" />
                </div>

                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ __('Nt: kipsplaat, isolatsioon, aknad...') }}"
                    class="w-full bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                />
            </div>
        </div>

        {{-- CATEGORY --}}
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                {{ __('Kategooria') }}
            </label>

            <select
                name="category"
                class="mt-1 w-full rounded-2xl border border-zinc-200 bg-white px-3 py-3 text-sm shadow-sm transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-800"
            >
                <option value="">{{ __('Kõik kategooriad') }}</option>

                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>
                        {{ $cat->name_et }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SORT --}}
        @if($showSort)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    {{ __('Sorteeri') }}
                </label>

                <select
                    name="sort"
                    class="mt-1 w-full rounded-2xl border border-zinc-200 bg-white px-3 py-3 text-sm shadow-sm transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-800"
                >
                    <option value="newest" @selected($sort === 'newest')>{{ __('Uusimad') }}</option>
                    <option value="oldest" @selected($sort === 'oldest')>{{ __('Vanimad') }}</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Odavamad') }}</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Kallimad') }}</option>
                </select>
            </div>
        @endif

        {{-- ACTIONS --}}
        <div class="mt-6 flex gap-2 md:col-span-2 md:justify-end">
            <button
                type="submit"
                class="inline-flex flex-1 items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-300 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus:ring-zinc-700"
            >
                {{ __('Otsi') }}
            </button>

            @if($showReset)
                <a
                    href="{{ $action }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:hover:bg-zinc-800/60"
                >
                    {{ __('Tühjenda') }}
                </a>
            @endif
        </div>
    </div>
</form>