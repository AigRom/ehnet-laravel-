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

    <div class="mx-auto w-full max-w-[1500px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3">
            <x-ui.back-button />

            <div>


                <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-zinc-600">
                    {{ __('Kasutaja profiil, aktiivsed kuulutused ja talle jäetud tagasiside.') }}
                </p>
            </div>
        </div>

        <div x-data="{ tab: 'listings' }" class="space-y-6">
            
                <x-users.profile-card
                    :user="$profileUser"
                    :role-label="$roleLabel"
                    :joined-year="$joinedYear"
                    class="border-0 bg-transparent p-0 shadow-none"
                />
            

            <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white p-2 shadow-sm">
                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                    <button
                        type="button"
                        @click="tab = 'listings'"
                        class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-extrabold transition sm:min-w-56"
                        :class="tab === 'listings'
                            ? 'bg-emerald-900 text-white shadow-sm'
                            : 'bg-white text-emerald-950 hover:bg-emerald-50 hover:text-emerald-800'"
                    >
                        <span class="sm:hidden">
                            {{ __('Kuulutused') }}
                        </span>

                        <span class="hidden sm:inline">
                            {{ __('Aktiivsed kuulutused') }}
                        </span>

                        <span class="ml-1 opacity-80">
                            ({{ $profileUser->active_listings_count ?? 0 }})
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="tab = 'reviews'"
                        class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-extrabold transition sm:min-w-44"
                        :class="tab === 'reviews'
                            ? 'bg-emerald-900 text-white shadow-sm'
                            : 'bg-white text-emerald-950 hover:bg-emerald-50 hover:text-emerald-800'"
                    >
                        {{ __('Tagasiside') }}

                        <span class="ml-1 opacity-80">
                            ({{ $reviewsTotal }})
                        </span>
                    </button>
                </div>
            </div>

            <section x-show="tab === 'listings'" x-cloak>

                @if($activeListings->count())
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach($activeListings as $listing)
                            <x-listings.card :listing="$listing" />
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-[1.5rem] border border-emerald-950/10 bg-white px-4 py-4 shadow-sm">
                        {{ $activeListings->links() }}
                    </div>
                @else
                    <div class="rounded-[2rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center shadow-sm">
                        <p class="text-base font-bold text-emerald-950">
                            {{ __('Aktiivseid kuulutusi ei ole.') }}
                        </p>

                        <p class="mt-2 text-sm font-medium text-zinc-500">
                            {{ __('Sellel müüjal ei ole praegu aktiivseid kuulutusi.') }}
                        </p>
                    </div>
                @endif
            </section>

            <section x-show="tab === 'reviews'" x-cloak>

                @if($reviews->count())
                    <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                        <aside class="self-start rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 ring-1 ring-amber-900/10">
                                    <x-icons.star class="h-7 w-7 text-amber-500" />
                                </div>

                                <div>
                                    <div class="text-4xl font-extrabold leading-none text-emerald-950">
                                        {{ number_format($averageRating, 1, ',', ' ') }}
                                    </div>

                                    <div class="mt-1 text-sm font-bold text-zinc-500">
                                        {{ __('keskmine hinnang') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 rounded-2xl bg-emerald-50/60 p-4 ring-1 ring-emerald-900/10">
                                <div class="text-base font-extrabold text-emerald-950">
                                    {{ $ratingLabel }}
                                </div>

                                <div class="mt-1 text-sm font-medium text-zinc-600">
                                    {{ trans_choice('Põhineb :count hinnangul|Põhineb :count hinnangul', $reviewsTotal, ['count' => $reviewsTotal]) }}
                                </div>
                            </div>
                        </aside>

                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <x-reviews.card :review="$review" />
                            @endforeach

                            @if($reviews->hasPages())
                                <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white px-4 py-4 shadow-sm">
                                    {{ $reviews->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="rounded-[2rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center shadow-sm">
                        <p class="text-base font-bold text-emerald-950">
                            {{ __('Tagasiside puudub.') }}
                        </p>

                        <p class="mt-2 text-sm font-medium text-zinc-500">
                            {{ __('Sellel kasutajal pole veel tagasisidet.') }}
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app.public>