@props([
    'review',
    'openState' => 'showReviewModal',
])

@php
    $reviewerName = $review->reviewer?->name ?? __('Kasutaja');
    $reviewedName = $review->reviewedUser?->name ?? __('Kasutaja');
@endphp

<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $openState }}"
        x-transition.opacity
        @keydown.escape.window="{{ $openState }} = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            @click="{{ $openState }} = false"
        ></div>

        <div
            x-show="{{ $openState }}"
            x-transition.scale.opacity.duration.200ms
            @click.stop
            class="relative z-10 w-full max-w-sm rounded-2xl bg-white shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3">
                <div class="text-sm font-semibold text-zinc-900">
                    {{ __('Tagasiside') }}
                </div>

                <button
                    type="button"
                    @click="{{ $openState }} = false"
                    class="rounded-full p-1 text-zinc-500 hover:bg-zinc-100"
                    aria-label="{{ __('Sulge') }}"
                >
                    ✕
                </button>
            </div>

            <div class="space-y-3 px-4 py-4">
                <div class="text-sm text-zinc-600">
                    {{ $reviewerName }} → {{ $reviewedName }}
                </div>

                <div class="text-lg font-semibold text-zinc-900">
                    ⭐ {{ $review->rating }}
                </div>

                @if($review->comment)
                    <div class="max-h-40 overflow-y-auto rounded-xl bg-zinc-50 px-3 py-2 text-sm text-zinc-700">
                        {{ $review->comment }}
                    </div>
                @endif

                <div class="text-xs text-zinc-500">
                    {{ $review->created_at?->format('d.m.Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</template>