<x-layouts.app.public :title="__('Minu kuulutused')">
    @php
        $status = $currentStatus ?? request('status', 'all');
        $sort = $currentSort ?? request('sort', 'newest');
        $query = $currentQuery ?? request('q');
        $categoryId = $currentCategoryId ?? request('category_id');
        $baseParams = request()->except('page');
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 md:px-0">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
                    {{ __('Minu kuulutused') }}
                </h1>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ __('Halda oma kuulutusi, jälgi staatuseid ja liigu kiiresti vajalike tegevusteni.') }}
                </p>
            </div>

            <a
                href="{{ route('listings.create') }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
            >
                {{ __('Lisa kuulutus') }}
            </a>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'all']))"
                :active="$status === 'all'"
            >
                {{ __('Kõik') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'active']))"
                :active="$status === 'active'"
            >
                {{ __('Aktiivsed') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'reserved']))"
                :active="$status === 'reserved'"
            >
                {{ __('Broneeritud') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'sold']))"
                :active="$status === 'sold'"
            >
                {{ __('Müüdud') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'draft']))"
                :active="$status === 'draft'"
            >
                {{ __('Mustandid') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'archived']))"
                :active="$status === 'archived'"
            >
                {{ __('Peatatud') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'expired']))"
                :active="$status === 'expired'"
            >
                {{ __('Aegunud') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('listings.mine', array_merge($baseParams, ['status' => 'deleted']))"
                :active="$status === 'deleted'"
            >
                {{ __('Kustutatud') }}
            </x-ui.filter-pill>
        </div>

        <form method="GET" action="{{ route('listings.mine') }}" class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-4">
                    <label class="mb-1 block text-sm font-medium text-zinc-700">
                        {{ __('Võtmesõna') }}
                    </label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="{{ __('Nt. kipsplaat') }}"
                        class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                    >
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-zinc-700">
                        {{ __('Staatus') }}
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="all" @selected($status === 'all')>{{ __('Kõik kuulutused') }}</option>
                        <option value="active" @selected($status === 'active')>{{ __('Aktiivsed') }}</option>
                        <option value="reserved" @selected($status === 'reserved')>{{ __('Broneeritud') }}</option>
                        <option value="sold" @selected($status === 'sold')>{{ __('Müüdud') }}</option>
                        <option value="archived" @selected($status === 'archived')>{{ __('Peatatud') }}</option>
                        <option value="draft" @selected($status === 'draft')>{{ __('Mustandid') }}</option>
                        <option value="pending" @selected($status === 'pending')>{{ __('Ootel') }}</option>
                        <option value="rejected" @selected($status === 'rejected')>{{ __('Tagasi lükatud') }}</option>
                        <option value="expired" @selected($status === 'expired')>{{ __('Aegunud') }}</option>
                        <option value="deleted" @selected($status === 'deleted')>{{ __('Kustutatud') }}</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-zinc-700">
                        {{ __('Kategooria') }}
                    </label>
                    <select
                        name="category_id"
                        class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="">{{ __('Kõik kategooriad') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>
                                {{ $cat->name_et }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-zinc-700">
                        {{ __('Sorteeri') }}
                    </label>
                    <select
                        name="sort"
                        class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="newest" @selected($sort === 'newest')>{{ __('Uuemad ees') }}</option>
                        <option value="oldest" @selected($sort === 'oldest')>{{ __('Vanemad ees') }}</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Hind kasvav') }}</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Hind kahanev') }}</option>
                        <option value="expires_soon" @selected($sort === 'expires_soon')>{{ __('Aegub peagi') }}</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-zinc-500">
                    {{ __('Filtreeri ja halda oma kuulutusi kiiremini.') }}
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('listings.mine') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                    >
                        {{ __('Lähtesta') }}
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800"
                    >
                        {{ __('Rakenda filtrid') }}
                    </button>
                </div>
            </div>
        </form>

        @if($listings->isEmpty())
            @if(!$hasAnyListings)
                <div class="rounded-3xl border border-dashed border-zinc-300 bg-white p-8 text-center shadow-sm">
                    <p class="text-base font-medium text-zinc-800">
                        {{ __('Sul pole veel kuulutusi.') }}
                    </p>
                    <p class="mt-2 text-sm text-zinc-500">
                        {{ __('Lisa esimene kuulutus ja hakka seda siin mugavalt haldama.') }}
                    </p>

                    <a
                        href="{{ route('listings.create') }}"
                        wire:navigate
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        {{ __('Lisa kuulutus') }}
                    </a>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-zinc-300 bg-white p-8 text-center shadow-sm">
                    <p class="text-base font-medium text-zinc-800">
                        {{ __('Selle otsingu või filtriga tulemusi ei leitud.') }}
                    </p>
                    <p class="mt-2 text-sm text-zinc-500">
                        {{ __('Proovi teisi filtreid või kuva kõik kuulutused.') }}
                    </p>

                    <a
                        href="{{ route('listings.mine') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800"
                    >
                        {{ __('Näita kõiki kuulutusi') }}
                    </a>
                </div>
            @endif
        @else
            <div class="space-y-3">
                @foreach($listings as $listing)
                    <a
                        href="{{ route('listings.mine.show', $listing) }}"
                        class="group block rounded-2xl border border-zinc-200 bg-white p-3 transition hover:border-zinc-300 hover:shadow-sm"
                    >
                        <div class="flex gap-4">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
                                @if($listing->coverImageUrl())
                                    <img src="{{ $listing->coverImageUrl() }}" class="h-full w-full object-cover" alt="">
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate font-medium text-zinc-900 group-hover:underline">
                                            {{ $listing->title }}
                                        </div>

                                        <div class="mt-1 text-sm text-zinc-500">
                                            {{ $listing->priceLabel() }}
                                            @if($listing->location)
                                                • {{ $listing->location->name }}
                                            @endif
                                        </div>
                                    </div>

                                    <x-ui.status-badge
                                        :status="$listing->status"
                                        :expired="$listing->isExpired()"
                                        class="whitespace-nowrap"
                                    >
                                        {{ $listing->statusLabel() }}
                                    </x-ui.status-badge>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500">
                                    <span>
                                        {{ __('Lisatud') }}:
                                        {{ optional($listing->published_at)->format('d.m.Y') ?? '—' }}
                                    </span>

                                    @if($listing->showExpiryDate())
                                        <span>
                                            {{ $listing->expiryLabel() }}
                                            {{ $listing->expiryDateText() }}
                                        </span>
                                    @endif

                                    @if($listing->status === 'reserved' && $listing->tradeBuyerName())
                                        <span class="text-amber-600">
                                            {{ __('Broneeritud: :name', ['name' => $listing->tradeBuyerName()]) }}
                                        </span>
                                    @endif

                                    @if($listing->status === 'sold' && $listing->tradeBuyerName())
                                        <span class="text-emerald-600">
                                            {{ __('Müüdud: :name', ['name' => $listing->tradeBuyerName()]) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if(method_exists($listings, 'links'))
                <div>
                    {{ $listings->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app.public>