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
        <div class="flex min-w-0 items-start gap-3">
            {{-- SCORE --}}
            <div class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-lg font-bold text-zinc-900 ring-1 ring-amber-100 sm:text-xl">
                <x-icons.star class="h-5 w-5 text-amber-500" />

                <span>{{ (int) $review->rating }}</span>
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

                <div class="mt-1 text-sm leading-6 text-zinc-700">
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
        <div class="shrink-0 whitespace-nowrap text-xs text-zinc-500">
            {{ $review->created_at?->format('d.m.Y') }}
        </div>
    </div>
</article>