<x-layouts.app.public :title="__('Minu kuulutused')">
    @php
        $status = $currentStatus ?? request('status', 'all');
        $sort = $currentSort ?? request('sort', 'newest');
        $query = $currentQuery ?? request('q', '');
        $categoryId = $currentCategoryId ?? request('category_id');
        $hasCategoryFilter = filled($categoryId);

        $baseParams = request()->except('page', 'listing');

        $activeListing = $activeListing ?? $listings->first();
        $activeListingId = $activeListing?->id;
    @endphp

    <div class="mx-auto max-w-[1600px] space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col gap-3">
            <x-ui.back-button
                :href="route('dashboard')"
                :label="__('Minu EHNET')"
            />

            <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950">
                {{ __('Minu kuulutused') }}
            </h1>
        </div>

        {{-- Status pills --}}
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
        </div>

        {{-- Compact filters --}}
        <form
            method="GET"
            action="{{ route('listings.mine') }}"
            class="rounded-[1.5rem] border border-emerald-950/10 bg-white p-3 shadow-sm sm:p-4"
        >
            <div class="grid grid-cols-1 gap-2 md:grid-cols-12">
                <div class="md:col-span-4">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-zinc-500">
                        {{ __('Otsi') }}
                    </label>

                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        placeholder="{{ __('Nt. kipsplaat') }}"
                        class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-3 text-sm font-medium text-emerald-950 outline-none transition placeholder:text-zinc-400 focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10"
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-zinc-500">
                        {{ __('Staatus') }}
                    </label>

                    <select
                        name="status"
                        class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-3 text-sm font-medium text-emerald-950 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10"
                    >
                        <option value="all" @selected($status === 'all')>{{ __('Kõik') }}</option>
                        <option value="active" @selected($status === 'active')>{{ __('Aktiivsed') }}</option>
                        <option value="reserved" @selected($status === 'reserved')>{{ __('Broneeritud') }}</option>
                        <option value="sold" @selected($status === 'sold')>{{ __('Müüdud') }}</option>
                        <option value="archived" @selected($status === 'archived')>{{ __('Peatatud') }}</option>
                        <option value="draft" @selected($status === 'draft')>{{ __('Mustandid') }}</option>
                        <option value="pending" @selected($status === 'pending')>{{ __('Ootel') }}</option>
                        <option value="rejected" @selected($status === 'rejected')>{{ __('Tagasi lükatud') }}</option>
                        <option value="expired" @selected($status === 'expired')>{{ __('Aegunud') }}</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-zinc-500">
                        {{ __('Kategooria') }}
                    </label>

                    <select
                        name="category_id"
                        @class([
                            'h-11 w-full rounded-2xl px-3 text-sm font-medium outline-none transition focus:ring-4',
                            'border border-emerald-900 bg-emerald-900 text-white focus:border-emerald-800 focus:bg-emerald-900 focus:ring-emerald-900/20' => $hasCategoryFilter,
                            'border border-emerald-950/10 bg-stone-50 text-emerald-950 focus:border-emerald-900/30 focus:bg-white focus:ring-emerald-900/10' => ! $hasCategoryFilter,
                        ])
                    >
                        <option value="" class="bg-white text-emerald-950">
                            {{ __('Kõik kategooriad') }}
                        </option>

                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}"
                                class="bg-white text-emerald-950"
                                @selected((string) $categoryId === (string) $cat->id)
                            >
                                {{ $cat->name_et }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-zinc-500">
                        {{ __('Sorteeri') }}
                    </label>

                    <select
                        name="sort"
                        class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-3 text-sm font-medium text-emerald-950 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10"
                    >
                        <option value="newest" @selected($sort === 'newest')>{{ __('Uuemad') }}</option>
                        <option value="oldest" @selected($sort === 'oldest')>{{ __('Vanemad') }}</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Hind ↑') }}</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Hind ↓') }}</option>
                        <option value="expires_soon" @selected($sort === 'expires_soon')>{{ __('Aegub peagi') }}</option>
                    </select>
                </div>

                <div class="md:col-span-1 md:flex md:items-end">
                    <button
                        type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-emerald-900 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                    >
                        {{ __('Otsi') }}
                    </button>
                </div>
            </div>
        </form>

        @if($listings->isEmpty())
            @if(!$hasAnyListings)
                <div class="rounded-[2rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center shadow-sm">
                    <p class="text-base font-bold text-emerald-950">
                        {{ __('Sul pole veel kuulutusi.') }}
                    </p>

                    <p class="mt-2 text-sm font-medium text-zinc-500">
                        {{ __('Lisa esimene kuulutus ja hakka seda siin mugavalt haldama.') }}
                    </p>

                    <a
                        href="{{ route('listings.create') }}"
                        wire:navigate
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-800"
                    >
                        {{ __('Lisa kuulutus') }}
                    </a>
                </div>
            @else
                <div class="rounded-[2rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center shadow-sm">
                    <p class="text-base font-bold text-emerald-950">
                        {{ __('Selle otsingu või filtriga tulemusi ei leitud.') }}
                    </p>

                    <p class="mt-2 text-sm font-medium text-zinc-500">
                        {{ __('Proovi teisi filtreid või kuva kõik kuulutused.') }}
                    </p>

                    <a
                        href="{{ route('listings.mine') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-800"
                    >
                        {{ __('Näita kõiki kuulutusi') }}
                    </a>
                </div>
            @endif
        @else
            {{-- Desktop split + mobile list --}}
            <div class="grid gap-5 lg:grid-cols-[430px_minmax(0,1fr)]">
                {{-- Left list --}}
                <section class="min-w-0 space-y-3">
                    <div class="space-y-2">
                        @foreach($listings as $listing)
                            @php
                                $isActiveListing = (int) $activeListingId === (int) $listing->id;

                                $desktopParams = array_merge(request()->query(), [
                                    'listing' => $listing->id,
                                ]);
                            @endphp

                            {{-- Mobile: opens show page --}}
                            <a
                                href="{{ route('listings.mine.show', $listing) }}"
                                class="block lg:hidden"
                            >
                                @include('user.listings.partials.list-card', [
                                    'listing' => $listing,
                                    'isActive' => false,
                                ])
                            </a>

                            {{-- Desktop: changes right panel --}}
                            <a
                                href="{{ route('listings.mine', $desktopParams) }}"
                                class="hidden lg:block"
                                wire:navigate
                            >
                                @include('user.listings.partials.list-card', [
                                    'listing' => $listing,
                                    'isActive' => $isActiveListing,
                                ])
                            </a>
                        @endforeach
                    </div>

                    @if(method_exists($listings, 'links') && $listings->hasPages())
                        <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white px-4 py-4 shadow-sm">
                            <div class="space-y-3">
                                <div class="text-sm font-medium text-zinc-500">
                                    {{ __('Kuvatakse') }}
                                    <span class="font-bold text-emerald-950">{{ $listings->firstItem() }}</span>
                                    –
                                    <span class="font-bold text-emerald-950">{{ $listings->lastItem() }}</span>
                                    {{ __('kokku') }}
                                    <span class="font-bold text-emerald-950">{{ $listings->total() }}</span>
                                    {{ __('kuulutusest') }}
                                </div>

                                <div class="min-w-0 overflow-x-auto">
                                    {{ $listings->onEachSide(1)->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Right detail --}}
                <section class="hidden min-w-0 lg:block">
                    <div class="rounded-[2rem] border border-emerald-950/10 bg-white/80 p-4 shadow-xl shadow-emerald-950/5 backdrop-blur">
                        @if($activeListing)
                            @include('user.listings.partials.detail-panel', [
                                'listing' => $activeListing,
                            ])
                        @else
                            <div class="flex min-h-[420px] items-center justify-center rounded-[1.5rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center">
                                <div>
                                    <p class="text-base font-bold text-emerald-950">
                                        {{ __('Vali kuulutus') }}
                                    </p>

                                    <p class="mt-2 text-sm font-medium text-zinc-500">
                                        {{ __('Detailvaade kuvatakse siin, kui vasakult kuulutus valida.') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-layouts.app.public>