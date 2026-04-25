@props([
    'action' => url('/listings'),
    'categories' => collect(),
    'counties' => collect(),
    'variant' => 'panel', // panel | inline
])

@php
    $q = request('q');
    $category = request('category');
    $county = request('county');

    $formClasses = $variant === 'panel'
        ? 'mt-6 rounded-[2rem] border border-emerald-950/10 bg-white p-4 shadow-xl shadow-emerald-950/5 md:sticky md:top-28 z-40'
        : 'rounded-[2rem] border border-emerald-950/10 bg-white p-3 shadow-2xl shadow-emerald-950/10';

    $fieldLabel = 'mb-2 block text-xs font-bold uppercase tracking-wide text-emerald-900/70';

    $selectBase = 'w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3.5 text-sm font-semibold text-emerald-950 transition focus:border-emerald-700/30 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-900/10';
@endphp

<form method="GET" action="{{ $action }}" class="{{ $formClasses }}">
    <div class="grid gap-3 lg:grid-cols-12 lg:items-end">

        {{-- SEARCH --}}
        <div class="lg:col-span-5">
            <label class="{{ $fieldLabel }}">
                {{ __('Mida otsid?') }}
            </label>

            <div class="flex min-h-[54px] items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 transition focus-within:border-emerald-700/30 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-900/10">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-900">
                    <x-icons.magnifying-glass class="h-5 w-5" />
                </div>

                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ __('Puit, plaadid, aknad, uksed...') }}"
                    class="w-full border-0 bg-transparent p-0 text-base font-semibold text-emerald-950 outline-none placeholder:text-zinc-400 focus:outline-none focus:ring-0"
                />
            </div>
        </div>

        {{-- CATEGORY --}}
        <div class="lg:col-span-3">
            <label class="{{ $fieldLabel }}">
                {{ __('Kategooria') }}
            </label>

            <select name="category" class="{{ $selectBase }}">
                <option value="">{{ __('Kõik kategooriad') }}</option>

                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>
                        {{ $cat->name_et }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- COUNTY --}}
        <div class="lg:col-span-3">
            <label class="{{ $fieldLabel }}">
                {{ __('Maakond') }}
            </label>

            <select name="county" class="{{ $selectBase }}">
                <option value="">{{ __('Kõik maakonnad') }}</option>

                @foreach($counties as $countyItem)
                    <option value="{{ $countyItem->ehak_code }}" @selected((string) $county === (string) $countyItem->ehak_code)>
                        {{ $countyItem->name_et }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SEARCH BUTTON --}}
        <div class="lg:col-span-1">
            <button
                type="submit"
                aria-label="{{ __('Otsi') }}"
                class="inline-flex min-h-[54px] w-full items-center justify-center rounded-2xl bg-emerald-900 text-white shadow-sm shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
            >
                <x-icons.magnifying-glass class="h-6 w-6" />
            </button>
        </div>

    </div>
</form>