@props([
    'listing' => null,
    'imageUrls' => [],
    'mode' => 'db', // db | preview
])

@php
    $isPreview = $mode === 'preview';
    $imageUrls = array_values(array_filter($imageUrls));
@endphp

<div
    class="w-full max-w-4xl rounded-2xl bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden"
    @if($isPreview)
        id="previewListingDetail"
        x-data="{ idx: 0, imgs: [] }"
        @listing-preview-images.window="
            imgs = ($event.detail?.images || []);
            idx = 0;
        "
    @else
        x-data="{ idx: 0, imgs: @js($imageUrls) }"
    @endif
>
    <div class="p-4 space-y-4">
        {{-- BIG cover image + arrows --}}
        <div class="relative">
            <button
                type="button"
                class="absolute left-2 top-1/2 -translate-y-1/2 z-10 text-white w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 flex items-center justify-center"
                @click="idx = (idx - 1 + imgs.length) % imgs.length"
                x-show="imgs.length > 1"
            >‹</button>

            <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 z-10 text-white w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 flex items-center justify-center"
                @click="idx = (idx + 1) % imgs.length"
                x-show="imgs.length > 1"
            >›</button>

            <div class="rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <img
                    class="w-full h-[45vh] object-contain"
                    :src="imgs[idx]"
                    alt=""
                    x-show="imgs.length"
                >
                <div class="w-full h-[45vh] flex items-center justify-center text-sm text-zinc-500"
                     x-show="!imgs.length">
                    {{ __('Pilte pole lisatud') }}
                </div>
            </div>

            <div class="mt-2 text-center text-xs text-zinc-500" x-show="imgs.length">
                <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
            </div>
        </div>

        {{-- Thumbnails --}}
        <div class="grid grid-cols-5 gap-2" x-show="imgs.length > 1">
            <template x-for="(src, i) in imgs" :key="src + '-' + i">
                <button
                    type="button"
                    class="relative aspect-square rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900"
                    :class="i === idx ? 'ring-2 ring-zinc-400' : ''"
                    @click="idx = i"
                >
                    <img :src="src" class="w-full h-full object-cover" alt="">
                </button>
            </template>
        </div>

        {{-- Listing text --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <div class="text-xl font-semibold truncate">
                        @if($isPreview)
                            <span id="previewTitle">—</span>
                        @else
                            {{ $listing->title }}
                        @endif
                    </div>

                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                        @if($isPreview)
                            <span id="previewCategory">—</span>
                            <span class="mx-2">•</span>
                            <span id="previewLocation">—</span>
                        @else
                            <span>{{ $listing->category?->name_et ?? '—' }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $listing->location?->full_label_et ?? '—' }}</span>
                        @endif
                    </div>

                    @unless($isPreview)
                        <div class="text-xs text-zinc-500">
                            {{ __('Staatus:') }} {{ $listing->statusLabel() }}
                        </div>
                    @endunless
                </div>

                <div class="text-right shrink-0">
                    <div class="text-lg font-semibold">
                        @if($isPreview)
                            <span id="previewPrice">—</span>
                        @else
                            @if($listing->price === null)
                                {{ __('Kokkuleppel') }}
                            @elseif((float)$listing->price == 0.0)
                                {{ __('Tasuta') }}
                            @else
                                {{ number_format((float)$listing->price, 2, '.', ' ') }}
                            @endif
                        @endif
                    </div>

                    <div class="text-xs text-zinc-500">
                        @if($isPreview)
                            EUR
                        @else
                            {{ $listing->currency ?? 'EUR' }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="prose prose-zinc dark:prose-invert max-w-none whitespace-pre-line">
                @if($isPreview)
                    <span id="previewDescription">—</span>
                @else
                    {{ $listing->description }}
                @endif
            </div>
        </div>
    </div>
</div>
