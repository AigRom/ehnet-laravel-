<x-layouts.app.public :title="$listing->title ?? __('Kuulutus')">
    @php
        $reservedTrade = $reservedTrade ?? null;
        $soldTrade = $soldTrade ?? null;
    @endphp

    <div class="mx-auto w-full max-w-[1500px] space-y-6 py-6 md:py-8">
        <div class="flex items-center justify-between">
            <x-ui.back-button />
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px] xl:grid-cols-[minmax(0,1fr)_420px] lg:items-start">
            <div class="min-w-0">
                <x-listings.detail
                    mode="db"
                    :listing="$listing"
                />
            </div>

            <aside class="space-y-4">
                <x-listings.seller-card
                    :seller="$listing->user"
                    :listing="$listing"
                    :profile-url="route('users.show', $listing->user)"
                />

                <x-listings.status-actions
                    :listing="$listing"
                    :reserved-trade="$reservedTrade"
                    :sold-trade="$soldTrade"
                />
            </aside>
        </div>

        @if($sellerListings->isNotEmpty())
            <section class="pt-4">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-zinc-900">
                        {{ __('Müüja teised kuulutused') }}
                        <span class="text-zinc-500">
                            ({{ $sellerListings->count() }})
                        </span>
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Vaata veel sama müüja aktiivseid kuulutusi.') }}
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach($sellerListings as $sellerListing)
                        <x-listings.card :listing="$sellerListing" />
                    @endforeach
                </div>
            </section>
        @endif

        @if($similarListings->isNotEmpty())
            <section class="pt-2">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-zinc-900">
                        {{ __('Sarnased kuulutused') }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Sama kategooria kuulutused, mis võiksid samuti huvi pakkuda.') }}
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach($similarListings as $similarListing)
                        <x-listings.card :listing="$similarListing" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app.public>