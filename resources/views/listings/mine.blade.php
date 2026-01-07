<x-layouts.app.sidebar :title="__('Minu kuulutused')">
    <flux:main>
        <div class="max-w-4xl space-y-6">

            <flux:heading size="xl">
                {{ __('Minu kuulutused') }}
            </flux:heading>

            {{-- Otsing / filtrid (UI-only) --}}
            <form method="GET" action="{{ route('listings.mine') }}" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    {{-- Staatus --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
                            {{ __('Kuulutused') }}
                        </label>
                        <select
                            name="status"
                            class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
                        >
                            <option value="all" @selected(request('status', 'all') === 'all')>{{ __('Kõik kuulutused') }}</option>
                            <option value="active" @selected(request('status') === 'active')>{{ __('Aktiivsed kuulutused') }}</option>
                            <option value="archived" @selected(request('status') === 'archived')>{{ __('Peatatud kuulutused') }}</option>
                            <option value="pending" @selected(request('status') === 'pending')>{{ __('Ootel kuulutused') }}</option>
                            <option value="expired" @selected(request('status') === 'expired')>{{ __('Aegunud kuulutused') }}</option>
                            <option value="sold" @selected(request('status') === 'sold')>{{ __('Müüdud kuulutused') }}</option>


                        </select>
                    </div>

                    {{-- Võtmesõna --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
                            {{ __('Võtmesõna') }}
                        </label>
                        <input
                            type="text"
                            value="{{ request('q') }}"
                            name="q"
                            placeholder="{{ __('Nt. kipsplaat') }}"
                            class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
                        >
                    </div>

                    {{-- Kategooria --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
                            {{ __('Kategooria') }}
                        </label>
                        <select
                            name="category_id"
                            class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
                        >
                            <option value="">{{ __('Kõik kategooriad') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string)request('category_id') === (string)$cat->id)>
                                    {{ $cat->name_et }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Otsi --}}
                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 px-4 py-3 font-medium"
                        >
                            {{ __('Otsi') }}
                        </button>
                    </div>
                </div>
            </form>


            {{-- Tühi olek --}}

            @if($listings->isEmpty())
                @if(!$hasAnyListings)
                    <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-6 text-center">
                        <p class="text-zinc-600 dark:text-zinc-400">
                            {{ __('Sul pole veel kuulutusi.') }}
                        </p>
                        <p class="mt-1 text-sm text-zinc-500">
                            {{ __('Peale kuulutuse lisamist saad neid hallata (muuta, kustutada).') }}
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-6 text-center">
                        <p class="text-zinc-600 dark:text-zinc-400">
                            {{ __('Selle otsingu/filtriga tulemusi ei leitud.') }}
                        </p>

                        <a
                            href="{{ route('listings.mine') }}"
                            class="mt-3 inline-flex items-center justify-center rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 px-4 py-2 font-medium"
                        >
                            {{ __('Näita kõiki kuulutusi') }}
                        </a>
                    </div>
                @endif
            @else
                <div class="space-y-3">
                    @foreach($listings as $listing)
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        <a
                                            href="{{ route('listings.mine.show', $listing) }}"
                                            class="font-medium text-zinc-900 dark:text-zinc-100 hover:underline truncate"
                                        >
                                            {{ $listing->title }}
                                        </a>

                                    </div>

                                    <div class="mt-1 text-sm text-zinc-500">
                                        {{ __('Staatus:') }} {{ $listing->statusLabel() }}
                                    </div>
                                </div>

                                <div class="text-sm text-zinc-500 whitespace-nowrap">
                                    {{ optional($listing->published_at)->format('d.m.Y') ?? '—' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif


            {{-- Lisa kuulutus --}}
            <div class="text-center">
                <flux:button
                    variant="primary"
                    :href="route('listings.create')"
                    wire:navigate
                >
                    {{ __('Lisa kuulutus') }}
                </flux:button>
            </div>

        </div>
    </flux:main>
</x-layouts.app.sidebar>
