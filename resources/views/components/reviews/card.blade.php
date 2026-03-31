@props([
    'review',
])

@php
    $reviewerName = $review->reviewer?->name ?? __('Kasutaja');
    $reviewerRole = $review->reviewerRoleLabel();
@endphp

<article class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex items-start justify-between gap-4">
        {{-- LEFT --}}
        <div class="flex items-start gap-3 min-w-0">
            {{-- SCORE --}}
            <div class="text-lg sm:text-xl font-semibold text-zinc-900 shrink-0">
                ⭐ {{ (int) $review->rating }}
            </div>

            {{-- NAME + ROLE + COMMENT --}}
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="font-semibold text-zinc-900">
                        {{ $reviewerName }}
                    </div>

                    @if($reviewerRole !== '')
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-600">
                            {{ $reviewerRole }}
                        </span>
                    @endif
                </div>

                <div class="mt-1 text-sm text-zinc-700">
                    @if(filled($review->comment))
                        {{ $review->comment }}
                    @else
                        <span class="text-zinc-500">
                            {{ __('Kasutaja ei kirjutanud kommentaari.') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- DATE --}}
        <div class="shrink-0 text-xs text-zinc-500 whitespace-nowrap">
            {{ $review->created_at?->format('d.m.Y') }}
        </div>
    </div>
</article>