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

    $formClasses = $variant === 'panel'
        ? 'mt-6 rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm md:sticky md:top-20 z-40'
        : 'mt-6';
@endphp

<form method="GET" action="{{ $action }}" class="{{ $formClasses }}">
    <div class="grid gap-4 md:grid-cols-12 md:items-end">

        {{-- SEARCH --}}
        <div class="{{ $showSort ? 'md:col-span-5' : 'md:col-span-7' }}">
            <label class="block text-sm font-medium text-zinc-700">
                {{ __('Otsi') }}
            </label>

            <div class="mt-1 flex items-center rounded-2xl border border-zinc-200 bg-zinc-50 transition
                        focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100">

                <div class="pl-4 text-zinc-400">
                    <x-icons.magnifying-glass class="h-5 w-5" />
                </div>

                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ __('Nt: kipsplaat, isolatsioon, aknad...') }}"
                    class="w-full bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400"
                />
            </div>
        </div>

        {{-- CATEGORY --}}
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-zinc-700">
                {{ __('Kategooria') }}
            </label>

            <select
                name="category"
                class="mt-1 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-900 transition
                       focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
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
                <label class="block text-sm font-medium text-zinc-700">
                    {{ __('Sorteeri') }}
                </label>

                <select
                    name="sort"
                    class="mt-1 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-900 transition
                           focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
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
                class="inline-flex flex-1 items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
            >
                {{ __('Otsi') }}
            </button>

            @if($showReset)
                <a
                    href="{{ $action }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                >
                    {{ __('Tühjenda') }}
                </a>
            @endif
        </div>

    </div>
</form>
