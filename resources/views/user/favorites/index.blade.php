<x-layouts.app.public :title="__('Lemmikud')">
    <div class="mx-auto max-w-6xl py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-zinc-900">
                {{ __('Minu lemmikud') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600">
                {{ __('Siia on salvestatud sinu lemmik kuulutused.') }}
            </p>
        </div>

        @if($listings->count())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($listings as $listing)
                    @php
                        $isTrashed = method_exists($listing, 'trashed') && $listing->trashed();

                        $isExpired = !$isTrashed
                            && $listing->status === 'published'
                            && $listing->expires_at
                            && $listing->expires_at->isPast();

                        $isDeleted = $isTrashed || $listing->status === 'deleted';
                        $isArchived = $listing->status === 'archived';
                        $isSold = $listing->status === 'sold';

                        $isClickable = !$isDeleted && !$isExpired && !$isArchived && !$isSold;

                        $statusLabel = null;
                        $statusClasses = null;

                        if ($isDeleted) {
                            $statusLabel = __('Kustutatud');
                            $statusClasses = 'bg-red-100 text-red-700 border-red-200';
                        } elseif ($isExpired) {
                            $statusLabel = __('Aegunud');
                            $statusClasses = 'bg-amber-100 text-amber-700 border-amber-200';
                        } elseif ($isArchived) {
                            $statusLabel = __('Müügist eemaldatud');
                            $statusClasses = 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        } elseif ($isSold) {
                            $statusLabel = __('Müüdud');
                            $statusClasses = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        }

                        $coverImage = $listing->coverImageUrl();
                    @endphp

                    <article class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm">
                        <div class="relative">
                            @if($isClickable)
                                <a href="{{ route('listings.show', $listing) }}" class="block">
                            @else
                                <div class="block cursor-default">
                            @endif
                                <div class="aspect-[4/3] w-full overflow-hidden bg-zinc-100">
                                    @if($coverImage)
                                        <img
                                            src="{{ $coverImage }}"
                                            alt="{{ $listing->title }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-sm text-zinc-400">
                                            {{ __('Pilt puudub') }}
                                        </div>
                                    @endif
                                </div>
                            @if($isClickable)
                                </a>
                            @else
                                </div>
                            @endif

                            <div class="absolute right-3 top-3">
                                <livewire:listings.favorite-toggle
                                    :listing="$listing"
                                    :key="'favorite-'.$listing->id"
                                />
                            </div>

                            @if($statusLabel)
                                <div class="absolute left-3 top-3">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            @if($isClickable)
                                <a href="{{ route('listings.show', $listing) }}" class="block">
                                    <h2 class="line-clamp-2 text-lg font-semibold text-zinc-900 hover:text-emerald-700">
                                        {{ $listing->title }}
                                    </h2>
                                </a>
                            @else
                                <h2 class="line-clamp-2 text-lg font-semibold text-zinc-900">
                                    {{ $listing->title }}
                                </h2>
                            @endif

                            <div class="mt-2 text-sm text-zinc-500">
                                {{ $listing->location?->full_label_et ?? $listing->location?->name ?? __('Asukoht lisamata') }}
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-4">
                                <div>
                                    @if(!is_null($listing->price))
                                        <div class="text-lg font-semibold text-zinc-900">
                                            {{ number_format((float) $listing->price, 2, ',', ' ') }} {{ $listing->currency ?? '€' }}
                                        </div>
                                    @else
                                        <div class="text-lg font-semibold text-zinc-900">
                                            {{ __('Kokkuleppel') }}
                                        </div>
                                    @endif
                                </div>

                                @if(!$isClickable && $statusLabel)
                                    <div class="text-right text-xs text-zinc-500">
                                        {{ __('Kuulutus ei ole enam avatav') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $listings->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 text-center">
                <h2 class="text-lg font-semibold text-zinc-900">
                    {{ __('Sul ei ole veel lemmikuid') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-600">
                    {{ __('Lisa kuulutusi lemmikutesse ja need ilmuvad siia.') }}
                </p>
            </div>
        @endif
    </div>
</x-layouts.app.public>