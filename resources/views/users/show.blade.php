<x-layouts.app.public :title="$profileUser->name ?? __('Profiil')">
    @php
        $joinedYear = optional($profileUser->created_at)?->format('Y');

        $roleLabel = $profileUser->company_name
            ? __('Ettevõte')
            : __('Eraisik');

        $isOwnProfile = auth()->check() && auth()->id() === $profileUser->id;

        $averageRating = $profileUser->averageRating();
        $reviewsTotal = $profileUser->reviewsCount();

        $ratingLabel = match (true) {
            $averageRating >= 4.8 => __('Suurepärane'),
            $averageRating >= 4.3 => __('Väga hea'),
            $averageRating >= 3.5 => __('Hea'),
            $averageRating >= 2.5 => __('Rahuldav'),
            $averageRating > 0 => __('Nõrk'),
            default => __('Tagasiside puudub'),
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:space-y-8 md:py-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <div class="flex items-center justify-between">
                <x-ui.back-button />

            </div>

        </div>

        <div x-data="{ tab: 'listings' }" class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm md:p-7">
                <x-users.profile-card
                    :user="$profileUser"
                    :role-label="$roleLabel"
                    :joined-year="$joinedYear"
                    class="border-0 bg-transparent p-0 shadow-none"
                />
            </div>

            <div class="border-b border-zinc-200">
                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-6 sm:overflow-x-auto">
                    <button
                        type="button"
                        @click="tab = 'listings'"
                        class="rounded-xl border px-2 py-3 text-center text-xs font-semibold leading-tight transition sm:rounded-none sm:border-0 sm:border-b-2 sm:px-1 sm:py-3 sm:text-xl sm:leading-normal whitespace-nowrap"
                        :class="tab === 'listings'
                            ? 'border-emerald-600 bg-emerald-50 text-zinc-900 sm:bg-transparent'
                            : 'border-zinc-200 bg-white text-zinc-500 hover:text-zinc-700 sm:border-transparent'"
                    >
                        <span class="sm:hidden">
                            {{ __('Kuulutused') }}
                        </span>
                        <span class="hidden sm:inline">
                            {{ __('Aktiivsed kuulutused') }}
                        </span>
                        <span class="text-zinc-500">
                            ({{ $profileUser->active_listings_count ?? 0 }})
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="tab = 'reviews'"
                        class="rounded-xl border px-2 py-3 text-center text-xs font-semibold leading-tight transition sm:rounded-none sm:border-0 sm:border-b-2 sm:px-1 sm:py-3 sm:text-xl sm:leading-normal whitespace-nowrap"
                        :class="tab === 'reviews'
                            ? 'border-emerald-600 bg-emerald-50 text-zinc-900 sm:bg-transparent'
                            : 'border-zinc-200 bg-white text-zinc-500 hover:text-zinc-700 sm:border-transparent'"
                    >
                        {{ __('Tagasiside') }}
                        <span class="text-zinc-500">
                            ({{ $reviewsTotal }})
                        </span>
                    </button>
                </div>
            </div>

            <section x-show="tab === 'listings'" x-cloak>
                <div class="mb-4">
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Kõik selle müüja aktiivsed kuulutused.') }}
                    </p>
                </div>

                @if($activeListings->count())
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach($activeListings as $listing)
                            <x-listings.card :listing="$listing" />
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $activeListings->links() }}
                    </div>
                @else
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 text-sm text-zinc-500 shadow-sm">
                        {{ __('Sellel müüjal ei ole praegu aktiivseid kuulutusi.') }}
                    </div>
                @endif
            </section>

            <section x-show="tab === 'reviews'" x-cloak>
                <div class="mb-4">
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Siin kuvatakse ostjate ja müüjate jäetud tagasiside.') }}
                    </p>
                </div>

                @if($reviews->count())
                    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                            <div class="text-4xl font-semibold leading-none text-zinc-900">
                                {{ number_format($averageRating, 1, ',', ' ') }}
                            </div>

                            <div class="mt-3 text-base font-semibold text-zinc-900">
                                {{ $ratingLabel }}
                            </div>

                            <div class="mt-1 text-sm text-zinc-500">
                                {{ trans_choice('Põhineb :count hinnangul|Põhineb :count hinnangul', $reviewsTotal, ['count' => $reviewsTotal]) }}
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <x-reviews.card :review="$review" />
                            @endforeach

                            <div class="pt-2">
                                {{ $reviews->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 text-sm text-zinc-500 shadow-sm">
                        {{ __('Sellel kasutajal pole veel tagasisidet.') }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app.public>