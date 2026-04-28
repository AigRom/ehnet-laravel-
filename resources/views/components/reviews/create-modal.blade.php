@props([
    'conversation',
    'trade',
    'openState' => 'showReviewModal',
    'title' => null,
    'description' => null,
])

@php
    $modalTitle = $title ?? __('Jäta tagasiside');
    $modalDescription = $description ?? __('Anna hinnang teisele osapoolele ja lisa soovi korral kommentaar.');

    $commentFieldId = 'review-comment-' . $trade->id;

    $hasErrors = $errors->has('rating') || $errors->has('comment');
    $initialRating = (int) old('rating', 0);
@endphp

<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $openState }} || {{ $hasErrors ? 'true' : 'false' }}"
        x-transition.opacity
        @keydown.escape.window="{{ $openState }} = false"
        class="fixed inset-0 z-[9999] flex items-end justify-center bg-zinc-950/60 p-3 backdrop-blur-sm sm:items-center sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="review-modal-title-{{ $trade->id }}"
    >
        <div
            class="absolute inset-0"
            @click="{{ $openState }} = false"
            aria-hidden="true"
        ></div>

        <div
            x-show="{{ $openState }} || {{ $hasErrors ? 'true' : 'false' }}"
            x-transition.scale.opacity.duration.200ms
            @click.stop
            class="relative z-10 w-full max-w-xl overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white shadow-2xl shadow-emerald-950/20"
        >
            {{-- Header --}}
            <div class="border-b border-emerald-950/10 bg-emerald-50/60 px-6 py-5 sm:px-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="mb-2 inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-emerald-900 ring-1 ring-emerald-900/10">
                            {{ __('Tagasiside') }}
                        </div>

                        <h2 id="review-modal-title-{{ $trade->id }}" class="text-2xl font-extrabold tracking-tight text-emerald-950">
                            {{ $modalTitle }}
                        </h2>

                        <p class="mt-2 text-sm font-medium leading-6 text-zinc-600">
                            {{ $modalDescription }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="{{ $openState }} = false"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-emerald-950/10 bg-white text-zinc-500 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                        aria-label="{{ __('Sulge') }}"
                        title="{{ __('Sulge') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('messages.trades.reviews.store', [$conversation, $trade]) }}"
                class="px-6 py-6 sm:px-7"
            >
                @csrf

                <div class="space-y-6">
                    <x-reviews.rating-stars
                        name="rating"
                        :initial-rating="$initialRating"
                        :label="__('Kuidas tehing sujus?')"
                    />

                    <div>
                        <label for="{{ $commentFieldId }}" class="mb-3 block text-sm font-bold text-emerald-950">
                            {{ __('Kommentaar') }}
                            <span class="font-medium text-zinc-500">({{ __('valikuline') }})</span>
                        </label>

                        <textarea
                            id="{{ $commentFieldId }}"
                            name="comment"
                            rows="5"
                            maxlength="1000"
                            class="w-full resize-none rounded-3xl border border-emerald-950/10 bg-stone-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10"
                            placeholder="{{ __('Kirjuta lühidalt, kuidas tehing sujus ja kas soovitad seda kasutajat teistele.') }}"
                        >{{ old('comment') }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-4">
                            @error('comment')
                                <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                            @else
                                <p class="text-xs font-medium leading-5 text-zinc-500">
                                    {{ __('Näiteks: kiire suhtlus, kaup vastas kirjeldusele, meeldiv tehing.') }}
                                </p>
                            @enderror

                            <span class="shrink-0 text-xs font-bold text-zinc-400">
                                {{ __('Max 1000') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="{{ $openState }} = false"
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-5 py-3 text-sm font-bold text-emerald-950 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                    >
                        {{ __('Tühista') }}
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                    >
                        {{ __('Salvesta tagasiside') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>