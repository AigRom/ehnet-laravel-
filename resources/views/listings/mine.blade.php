<x-layouts.app.public :title="__('Minu kuulutused')">
    <div class="mx-auto max-w-4xl space-y-6">

        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
            {{ __('Minu kuulutused') }}
        </h1>

        {{-- Otsing / filtrid (UI-only) --}}
        <form method="GET" action="{{ route('listings.mine') }}" class="space-y-3">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                {{-- Staatus --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Kuulutused') }}
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >
                        <option value="all" @selected(request('status', 'all') === 'all')>{{ __('Kõik kuulutused') }}</option>
                        <option value="active" @selected(request('status') === 'active')>{{ __('Aktiivsed') }}</option>
                        <option value="archived" @selected(request('status') === 'archived')>{{ __('Peatatud') }}</option>
                        <option value="draft" @selected(request('status') === 'draft')>{{ __('Mustandid') }}</option>
                        {{-- OOTEL KUULUTUSED TEHAKSE ADMIN/MODEREERIMISEGA --}}
                        {{-- <option value="pending" @selected(request('status') === 'pending')>{{ __('Ootel kuulutused') }}</option> --}}
                        <option value="expired" @selected(request('status') === 'expired')>{{ __('Aegunud') }}</option>
                        <option value="sold" @selected(request('status') === 'sold')>{{ __('Müüdud') }}</option>
                    </select>
                </div>

                {{-- Võtmesõna --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Võtmesõna') }}
                    </label>
                    <input
                        type="text"
                        value="{{ request('q') }}"
                        name="q"
                        placeholder="{{ __('Nt. kipsplaat') }}"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >
                </div>

                {{-- Kategooria --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Kategooria') }}
                    </label>
                    <select
                        name="category_id"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30"
                    >
                        <option value="">{{ __('Kõik kategooriad') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                                {{ $cat->name_et }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Otsi --}}
                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        {{ __('Otsi') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Tühi olek --}}
        @if($listings->isEmpty())
            @if(!$hasAnyListings)
                <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('Sul pole veel kuulutusi.') }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-500">
                        {{ __('Peale kuulutuse lisamist saad neid hallata (muuta, kustutada).') }}
                    </p>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ __('Selle otsingu/filtriga tulemusi ei leitud.') }}
                    </p>

                    <a
                        href="{{ route('listings.mine') }}"
                        class="mt-3 inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        {{ __('Näita kõiki kuulutusi') }}
                    </a>
                </div>
            @endif
        @else
            <div class="space-y-3">
                @foreach($listings as $listing)
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-zinc-900 dark:text-zinc-100">
                                    <a
                                        href="{{ route('listings.mine.show', $listing) }}"
                                        class="truncate font-medium text-zinc-900 hover:underline dark:text-zinc-100"
                                    >
                                        {{ $listing->title }}
                                    </a>
                                </div>

                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Staatus:') }} {{ $listing->statusLabel() }}
                                </div>
                            </div>

                            <div class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ optional($listing->published_at)->format('d.m.Y') ?? '—' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Lisa kuulutus --}}
        <div class="text-center">
            <a
                href="{{ route('listings.create') }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
            >
                {{ __('Lisa kuulutus') }}
            </a>
        </div>

    </div>
</x-layouts.app.public>