<x-layouts.app.public :title="__('Minu ostud')">
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
                    {{ __('Minu ostud') }}
                </h1>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ __('Jälgi oma ostusoove, broneeringuid ja lõpetatud tehinguid.') }}
                </p>
            </div>

            <a
                href="{{ route('listings.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
            >
                {{ __('Sirvi kuulutusi') }}
            </a>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-ui.filter-pill
                :href="route('purchases.index', array_merge($baseParams, ['status' => 'all']))"
                :active="$status === 'all'"
            >
                {{ __('Kõik') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('purchases.index', array_merge($baseParams, ['status' => 'interest']))"
                :active="$status === 'interest'"
            >
                {{ __('Ostusoovid') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('purchases.index', array_merge($baseParams, ['status' => 'reserved']))"
                :active="$status === 'reserved'"
            >
                {{ __('Broneeritud') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('purchases.index', array_merge($baseParams, ['status' => 'awaiting_confirmation']))"
                :active="$status === 'awaiting_confirmation'"
            >
                {{ __('Ootan kinnitust') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('purchases.index', array_merge($baseParams, ['status' => 'completed']))"
                :active="$status === 'completed'"
            >
                {{ __('Lõpetatud') }}
            </x-ui.filter-pill>

            <x-ui.filter-pill
                :href="route('purchases.index', array_merge($baseParams, ['status' => 'cancelled']))"
                :active="$status === 'cancelled'"
            >
                {{ __('Katkestatud') }}
            </x-ui.filter-pill>
        </div>

        <form method="GET" action="{{ route('purchases.index') }}" class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
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
                        <option value="all" @selected($status === 'all')>{{ __('Kõik ostud') }}</option>
                        <option value="interest" @selected($status === 'interest')>{{ __('Ostusoovid') }}</option>
                        <option value="reserved" @selected($status === 'reserved')>{{ __('Broneeritud') }}</option>
                        <option value="awaiting_confirmation" @selected($status === 'awaiting_confirmation')>{{ __('Ootan kinnitust') }}</option>
                        <option value="completed" @selected($status === 'completed')>{{ __('Lõpetatud') }}</option>
                        <option value="cancelled" @selected($status === 'cancelled')>{{ __('Katkestatud') }}</option>
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
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-zinc-500">
                    {{ __('Filtreeri ja halda oma oste kiiremini.') }}
                </div>

                <div class="flex gap-2">
                    <a
                        href="{{ route('purchases.index') }}"
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

        @if($purchases->isEmpty())
            @if(!$hasAnyPurchases)
                <div class="rounded-3xl border border-dashed border-zinc-300 bg-white p-8 text-center shadow-sm">
                    <p class="text-base font-medium text-zinc-800">
                        {{ __('Sul pole veel oste ega ostusoove.') }}
                    </p>
                    <p class="mt-2 text-sm text-zinc-500">
                        {{ __('Sirvi kuulutusi ja alusta vestlust, et oma esimesed ostud teha.') }}
                    </p>

                    <a
                        href="{{ route('listings.index') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        {{ __('Sirvi kuulutusi') }}
                    </a>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-zinc-300 bg-white p-8 text-center shadow-sm">
                    <p class="text-base font-medium text-zinc-800">
                        {{ __('Selle otsingu või filtriga tulemusi ei leitud.') }}
                    </p>
                    <p class="mt-2 text-sm text-zinc-500">
                        {{ __('Proovi teisi filtreid või kuva kõik ostud.') }}
                    </p>

                    <a
                        href="{{ route('purchases.index') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800"
                    >
                        {{ __('Näita kõiki oste') }}
                    </a>
                </div>
            @endif
        @else
            <div class="space-y-3">
                @foreach($purchases as $trade)
                    @php
                        $listing = $trade->listing;
                        $seller = $trade->seller;

                        $statusLabel = match ($trade->status) {
                            'interest' => __('Ostusoov'),
                            'reserved' => __('Broneeritud'),
                            'awaiting_confirmation' => __('Ootan kinnitust'),
                            'completed' => __('Lõpetatud'),
                            'cancelled' => __('Katkestatud'),
                            default => __('—'),
                        };

                        $statusClasses = match ($trade->status) {
                            'interest' => 'border-sky-200 bg-sky-100 text-sky-800',
                            'reserved' => 'border-amber-200 bg-amber-100 text-amber-800',
                            'awaiting_confirmation' => 'border-violet-200 bg-violet-100 text-violet-800',
                            'completed' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
                            'cancelled' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
                            default => 'border-zinc-200 bg-zinc-100 text-zinc-700',
                        };

                        $dateText =
                            $trade->buyer_confirmed_received_at?->format('d.m.Y')
                            ?? $trade->completed_at?->format('d.m.Y')
                            ?? $trade->awaiting_confirmation_at?->format('d.m.Y')
                            ?? $trade->reserved_at?->format('d.m.Y')
                            ?? $trade->created_at?->format('d.m.Y')
                            ?? '—';

                        $helpText = match ($trade->status) {
                            'reserved' => __('Broneering aktiivne'),
                            'awaiting_confirmation' => __('Ootab sinu kinnitust'),
                            'completed' => __('Tehing lõpetatud'),
                            default => null,
                        };

                        $helpClasses = match ($trade->status) {
                            'reserved' => 'text-amber-600',
                            'awaiting_confirmation' => 'text-violet-600',
                            'completed' => 'text-emerald-600',
                            default => 'text-zinc-500',
                        };
                    @endphp

                    <a
                        href="{{ route('purchases.show', $trade) }}"
                        class="group block rounded-2xl border border-zinc-200 bg-white p-3 transition hover:border-zinc-300 hover:shadow-sm"
                    >
                        <div class="flex gap-4">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
                                <img
                                    src="{{ $listing?->coverThumbUrlOrPlaceholder() ?? asset('images/placeholder.png') }}"
                                    class="h-full w-full object-cover"
                                    alt=""
                                >
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate font-medium text-zinc-900 group-hover:underline">
                                            {{ $listing?->title ?? __('Kuulutus on eemaldatud') }}
                                        </div>

                                        <div class="mt-1 text-sm text-zinc-500">
                                            {{ $listing?->priceLabel() ?? __('—') }}
                                            @if($listing?->location)
                                                • {{ $listing->location->name }}
                                            @endif
                                        </div>
                                    </div>

                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium whitespace-nowrap {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500">
                                    <span>
                                        {{ __('Müüja') }}:
                                        {{ $seller?->company_name ?? $seller?->name ?? '—' }}
                                    </span>

                                    <span>
                                        {{ __('Kuupäev') }}:
                                        {{ $dateText }}
                                    </span>

                                    @if($helpText)
                                        <span class="{{ $helpClasses }}">
                                            {{ $helpText }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if(method_exists($purchases, 'links'))
                <div>
                    {{ $purchases->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app.public>