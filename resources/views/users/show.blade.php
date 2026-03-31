<x-layouts.app.public :title="$profileUser->name ?? __('Profiil')">
    @php
        $joinedYear = optional($profileUser->created_at)?->format('Y');

        $roleLabel = $profileUser->company_name
            ? __('Ettevõte')
            : __('Eraisik');

        $isOwnProfile = auth()->check() && auth()->id() === $profileUser->id;
        $showGuestNotice = !auth()->check();

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
        <div class="flex items-center justify-between gap-4">
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

        <div x-data="{ tab: 'listings' }" class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 md:p-7 shadow-sm">
                <x-users.profile-card
                    :user="$profileUser"
                    :role-label="$roleLabel"
                    :joined-year="$joinedYear"
                    class="border-0 bg-transparent p-0 shadow-none"
                />

                @if($showGuestNotice)
                    <div class="mt-5 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 text-center">
                        <p class="mx-auto max-w-sm text-sm leading-6 text-zinc-600">
                            {{ __('Logi sisse, et näha profiili detailsemalt ja saata sõnumeid.') }}
                        </p>

                        <div class="mt-4">
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800"
                            >
                                {{ __('Logi sisse') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="border-b border-zinc-200">
                <div class="flex items-center gap-6 overflow-x-auto">
                    <button
                        type="button"
                        @click="tab = 'listings'"
                        class="border-b-2 px-1 py-3 text-xl font-semibold whitespace-nowrap transition"
                        :class="tab === 'listings'
                            ? 'border-emerald-600 text-zinc-900'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700'"
                    >
                        {{ __('Aktiivsed kuulutused') }}
                        <span class="text-zinc-500">
                            ({{ $profileUser->active_listings_count ?? 0 }})
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="tab = 'reviews'"
                        class="border-b-2 px-1 py-3 text-xl font-semibold whitespace-nowrap transition"
                        :class="tab === 'reviews'
                            ? 'border-emerald-600 text-zinc-900'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700'"
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