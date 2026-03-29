<x-layouts.app.public :title="$listing->title ?? __('Kuulutus')">
    @php
        $reservedTrade = $reservedTrade ?? null;
        $soldTrade = $soldTrade ?? null;

        $isExpired = $listing->status === 'published'
            && $listing->expires_at
            && $listing->expires_at->isPast();

        $isPubliclyAvailable = $listing->status === 'published' && ! $isExpired;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 md:py-8 space-y-6">

        <div class="flex items-center justify-between">
            <a
                href="{{ url()->previous() }}"
                class="text-sm text-blue-600 hover:underline"
            >
                ← {{ __('Tagasi') }}
            </a>

            <a
                href="{{ route('listings.index') }}"
                class="text-sm text-zinc-600 hover:underline"
            >
                {{ __('Kõik kuulutused') }}
            </a>
        </div>

        @if($listing->status === 'deleted')
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ __('See kuulutus on kustutatud ja ei ole enam avalikult saadaval.') }}
            </div>
        @elseif($listing->status === 'sold')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                @if($soldTrade?->buyer)
                    {{ __('See kuulutus on müüdud kasutajale :name.', ['name' => $soldTrade->buyer->name]) }}
                @else
                    {{ __('See kuulutus on müüdud.') }}
                @endif
            </div>
        @elseif($listing->status === 'reserved')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                @if($reservedTrade?->buyer && auth()->check() && (int) $reservedTrade->buyer_id === (int) auth()->id())
                    {{ __('See kuulutus on broneeritud sulle.') }}
                @elseif($reservedTrade?->buyer)
                    {{ __('See kuulutus on broneeritud kasutajale :name.', ['name' => $reservedTrade->buyer->name]) }}
                @else
                    {{ __('See kuulutus on broneeritud.') }}
                @endif
            </div>
        @elseif($listing->status === 'archived')
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600">
                {{ __('See kuulutus on müügist eemaldatud.') }}
            </div>
        @elseif($isExpired)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ __('See kuulutus on aegunud.') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
            <div class="min-w-0">
                <x-listings.detail
                    mode="db"
                    :listing="$listing"
                />
            </div>

            <x-listings.seller-card
                :seller="$listing->user"
                :listing="$listing"
                :is-own-listing="auth()->check() && auth()->id() === $listing->user_id"
                :is-authenticated="auth()->check()"
                :message-action="
                    $isPubliclyAvailable && auth()->check() && auth()->id() !== $listing->user_id
                        ? route('listings.conversation.open', $listing)
                        : null
                "
                :buy-intent-action="
                    $isPubliclyAvailable && auth()->check() && auth()->id() !== $listing->user_id
                        ? route('listings.buy-intent', $listing)
                        : null
                "
                :complete-trade-action="$reservedTrade ? route('messages.complete', $reservedTrade->conversation_id) : null"
                :reserved-trade="$reservedTrade"
                :sold-trade="$soldTrade"
                :active-listings-count="$listing->user->active_listings_count"
                :profile-url="route('users.show', $listing->user)"
            />
        </div>

        @if($sellerListings->isNotEmpty())
            <section class="pt-4">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-zinc-900">
                        {{ __('Müüja teised kuulutused') }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Vaata veel sama müüja aktiivseid kuulutusi.') }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($similarListings as $similarListing)
                        <x-listings.card :listing="$similarListing" />
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-layouts.app.public>