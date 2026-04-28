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
        'class' => 'rounded-[1.75rem] border border-emerald-950/10 bg-white p-5 shadow-sm transition sm:p-6',
    ]) }}
>
    <div class="flex items-start gap-4 md:gap-5">
        <x-ui.avatar :user="$user" size="h-16 w-16 md:h-20 md:w-20" />

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                <h3 class="truncate text-xl font-extrabold tracking-tight text-emerald-950 md:text-2xl">
                    {{ $user->name ?? __('Kasutaja') }}
                </h3>

                @if($roleLabel)
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-900 ring-1 ring-emerald-900/10">
                        {{ $roleLabel }}
                    </span>
                @endif
            </div>

            @if($hasMeta)
                <div class="mt-3 space-y-1.5 text-sm font-medium text-zinc-500 md:text-base">
                    @if($joinedYear)
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4 shrink-0 text-emerald-800/70"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2ZM3.5 8.5v6.75c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25V8.5h-13Z" clip-rule="evenodd" />
                            </svg>

                            <span>
                                {{ __('Kasutaja alates :year', ['year' => $joinedYear]) }}
                            </span>
                        </div>
                    @endif

                    @if($locationLabel)
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4 shrink-0 text-emerald-800/70"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M9.69 18.933l.31.2.31-.2C14.4 16.36 18 12.28 18 8.5 18 4.91 15.09 2 11.5 2S5 4.91 5 8.5c0 3.78 3.6 7.86 4.69 10.433zM11.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" clip-rule="evenodd" />
                            </svg>

                            <span class="truncate">
                                {{ $locationLabel }}
                            </span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm md:text-base">
                @if($hasReviews)
                    <span class="inline-flex items-center gap-1.5 font-extrabold text-emerald-950">
                        <x-icons.star class="h-4 w-4 text-amber-500 md:h-5 md:w-5" />

                        {{ number_format($scoreValue, 1, ',', ' ') }}
                    </span>

                    <span class="font-medium text-zinc-500">
                        {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCountValue, ['count' => $reviewsCountValue]) }}
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-sm font-bold text-zinc-600">
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