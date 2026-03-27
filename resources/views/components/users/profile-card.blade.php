@props([
    'user',
    'roleLabel' => null,
    'score' => null,
    'reviewsCount' => null,
    'locationLabel' => null,
    'joinedYear' => null,
])

@php
    $hasMeta = $joinedYear || $locationLabel;
@endphp

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition'
]) }}>
    <div class="flex items-start gap-4">
        <x-ui.avatar :user="$user" size="h-14 w-14" />

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <h3 class="text-base font-semibold text-zinc-900">
                    {{ $user->name ?? __('Kasutaja') }}
                </h3>

                @if($roleLabel)
                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600">
                        {{ $roleLabel }}
                    </span>
                @endif
            </div>

            @if($hasMeta)
                <div class="mt-1 space-y-1 text-sm text-zinc-500">
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

            @if(!is_null($score) || !is_null($reviewsCount))
                <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                    @if(!is_null($score))
                        <span class="font-semibold text-zinc-900">
                            {{ number_format((float) $score, 1, ',', ' ') }}
                        </span>
                    @endif

                    @if(!is_null($reviewsCount))
                        <span class="text-zinc-500">
                            {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCount, ['count' => $reviewsCount]) }}
                        </span>
                    @endif
                </div>
            @endif

            @if($slot->isNotEmpty())
                <div class="mt-4">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>