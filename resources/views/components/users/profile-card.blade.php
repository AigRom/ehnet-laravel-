@props([
    'user',
    'roleLabel' => null,
    'locationLabel' => null,
    'joinedYear' => null,
])

@php
    $hasMeta = filled($joinedYear) || filled($locationLabel);

    $reviewsCountValue = $user->reviewsCount();
    $scoreValue = $user->averageRating();
    $hasReviews = $user->hasReviews();
@endphp

<div
    {{ $attributes->merge([
        'class' => 'rounded-2xl border border-zinc-200 bg-white p-5 md:p-6 shadow-sm transition',
    ]) }}
>
    <div class="flex items-start gap-4 md:gap-5">
        <x-ui.avatar :user="$user" size="h-16 w-16 md:h-20 md:w-20" />

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                <h3 class="text-lg md:text-xl font-semibold text-zinc-900">
                    {{ $user->name ?? __('Kasutaja') }}
                </h3>

                @if($roleLabel)
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600">
                        {{ $roleLabel }}
                    </span>
                @endif
            </div>

            @if($hasMeta)
                <div class="mt-2 space-y-1 text-sm text-zinc-500 md:text-base">
                    @if($joinedYear)
                        <div>
                            {{ __('Kasutaja alates :year', ['year' => $joinedYear]) }}
                        </div>
                    @endif

                    @if($locationLabel)
                        <div>
                            {{ $locationLabel }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm md:text-base">
                @if($hasReviews)
                    <span class="font-semibold text-zinc-900">
                        ⭐ {{ number_format($scoreValue, 1, ',', ' ') }}
                    </span>

                    <span class="text-zinc-500">
                        {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCountValue, ['count' => $reviewsCountValue]) }}
                    </span>
                @else
                    <span class="text-zinc-500">
                        {{ __('Tagasiside puudub') }}
                    </span>
                @endif
            </div>

            @if($slot->isNotEmpty())
                <div class="mt-5">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>