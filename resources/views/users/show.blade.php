<x-layouts.app.public :title="$profileUser->name ?? __('Profiil')">
    @php
        $joinedYear = optional($profileUser->created_at)?->format('Y');

        $locationLabel = $profileUser->location?->full_label_et
            ?? $profileUser->location?->name
            ?? __('Asukoht lisamata');

        $roleLabel = $profileUser->company_name
            ? __('Ettevõte')
            : __('Eraisik');

        $isOwnProfile = auth()->check() && auth()->id() === $profileUser->id;
        $showGuestNotice = !auth()->check();
        $showMessageNotice = auth()->check() && !$isOwnProfile;
    @endphp

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-6 md:py-8">
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

        <div class="grid gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
            <aside class="space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <x-users.profile-card
                        :user="$profileUser"
                        :role-label="$roleLabel"
                        :joined-year="$joinedYear"
                        :location-label="$locationLabel"
                        :score="$score"
                        :reviews-count="$reviewsCount"
                        class="border-0 bg-transparent p-0 shadow-none"
                    />

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-zinc-50 px-3 py-3">
                            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                                {{ __('Aktiivsed kuulutused') }}
                            </div>

                            <div class="mt-1 text-base font-semibold text-zinc-900">
                                {{ $profileUser->active_listings_count ?? 0 }}
                            </div>
                        </div>

                        <div class="rounded-xl bg-zinc-50 px-3 py-3">
                            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                                {{ __('Tagasiside') }}
                            </div>

                            <div class="mt-1 text-base font-semibold text-zinc-900">
                                @if(!is_null($score))
                                    {{ number_format((float) $score, 1, ',', ' ') }}

                                    @if(!is_null($reviewsCount))
                                        <span class="text-sm font-normal text-zinc-500">
                                            ({{ $reviewsCount }})
                                        </span>
                                    @endif
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($showMessageNotice)
                        <div class="mt-4 rounded-xl bg-zinc-50 px-4 py-3 text-sm text-zinc-600">
                            {{ __('Sõnumi saatmine müüjale toimub tema kuulutuse kaudu.') }}
                        </div>
                    @endif

                    @if($showGuestNotice)
                        <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 text-center">
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
            </aside>

            <div class="min-w-0 space-y-8">
                <section>
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold text-zinc-900">
                            {{ __('Aktiivsed kuulutused') }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            {{ __('Kõik selle müüja aktiivsed kuulutused.') }}
                        </p>
                    </div>

                    @if($activeListings->count())
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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

                <section>
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold text-zinc-900">
                            {{ __('Tagasiside') }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            {{ __('Siia kuvatakse hiljem ostjate jäetud tagasiside.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 text-sm text-zinc-500 shadow-sm">
                        {{ __('Tagasiside loogika lisandub hiljem.') }}
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app.public>