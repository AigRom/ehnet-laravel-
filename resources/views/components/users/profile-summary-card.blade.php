@props([
    'user',
    'roleLabel' => null,
    'score' => null,
    'reviewsCount' => null,
])

@php
    $joinedYear = optional($user->created_at)?->format('Y');
@endphp

<div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-start gap-4">

        {{-- Profiilipildi koht --}}
        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
            @if(!empty($user->profile_photo_url))
                <img
                    src="{{ $user->profile_photo_url }}"
                    alt="{{ $user->name }}"
                    class="h-full w-full object-cover"
                >
            @else
                <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                    {{ $user->initials() }}
                </span>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $user->name ?? __('Kasutaja') }}
                </h3>

                @if($roleLabel)
                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $roleLabel }}
                    </span>
                @endif
            </div>

            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                @if($joinedYear)
                    {{ __('Kasutaja alates :year', ['year' => $joinedYear]) }}
                @else
                    {{ __('Kasutaja') }}
                @endif
            </div>

            @if(!is_null($score) || !is_null($reviewsCount))
                <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                    @if(!is_null($score))
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ number_format((float) $score, 1, ',', ' ') }}
                        </span>
                    @endif

                    @if(!is_null($reviewsCount))
                        <span class="text-zinc-500 dark:text-zinc-400">
                            {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCount, ['count' => $reviewsCount]) }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>