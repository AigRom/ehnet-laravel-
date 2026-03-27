<x-layouts.app.public :title="$listing->title ?? __('Kuulutus')">
    @php
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
                class="text-sm text-zinc-600 hover:underline dark:text-zinc-300"
            >
                {{ __('Kõik kuulutused') }}
            </a>
        </div>

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