{{-- resources/views/components/listings/detail.blade.php --}}
@props([
    'listing' => null,
    'imageUrls' => [],
    'mode' => 'db', // db | preview
])

@php
    use Illuminate\Support\Facades\Storage;

    $isPreview = $mode === 'preview';

    if (!$isPreview && empty($imageUrls) && $listing && $listing->relationLoaded('images')) {
        $imageUrls = $listing->images
            ->map(fn ($img) => $img->path ? Storage::url($img->path) : null)
            ->filter()
            ->values()
            ->all();
    }

    $imageUrls = array_values(array_filter($imageUrls));

    $title = $listing?->title ?? '—';
    $desc = $listing?->description ?? '—';
    $cat = $listing?->category?->name_et ?? '—';
    $loc = $listing?->location?->full_label_et ?? '—';

    $condLabel = ($listing && method_exists($listing, 'conditionLabel'))
        ? $listing->conditionLabel()
        : '—';

    $deliveryLabels = $listing?->deliveryOptionsLabels() ?? [];

    $priceText = $listing && method_exists($listing, 'priceLabel')
        ? $listing->priceLabel()
        : 'Kokkuleppel';
@endphp

<div
    class="w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm"
    x-data="{
        idx: 0,
        imgs: @js($isPreview ? [] : $imageUrls),

        zoomOpen: false,
        openZoom(i = null) {
            if (this.imgs.length === 0) return;
            if (i !== null) this.idx = i;
            this.zoomOpen = true;
            document.documentElement.classList.add('overflow-hidden');
        },
        closeZoom() {
            this.zoomOpen = false;
            document.documentElement.classList.remove('overflow-hidden');
        },
        next() {
            if (this.imgs.length === 0) return;
            this.idx = (this.idx + 1) % this.imgs.length;
        },
        prev() {
            if (this.imgs.length === 0) return;
            this.idx = (this.idx - 1 + this.imgs.length) % this.imgs.length;
        },

        title: @js($isPreview ? '—' : $title),
        category: @js($isPreview ? '—' : $cat),
        location: @js($isPreview ? '—' : $loc),
        description: @js($isPreview ? '—' : $desc),
        condition: @js($isPreview ? '—' : $condLabel),
        delivery: @js($isPreview ? [] : $deliveryLabels),
        price: @js($isPreview ? 'Kokkuleppel' : $priceText),
    }"
    @listing-preview-update.window="
        title = $event.detail?.title ?? title;
        category = $event.detail?.category ?? category;
        location = $event.detail?.location ?? location;
        description = $event.detail?.description ?? description;
        condition = $event.detail?.condition ?? condition;
        delivery = $event.detail?.delivery ?? delivery;
        price = $event.detail?.price ?? price;
    "
    @listing-preview-images.window="
        imgs = ($event.detail?.images || []);
        idx = 0;
    "
    @keydown.window.escape="if (zoomOpen) closeZoom()"
>
    <div class="space-y-5 p-4 md:space-y-6 md:p-6">
        <div class="grid gap-5 md:grid-cols-12">
            <div class="space-y-3 md:col-span-7">
                <div class="group relative">
                    <button
                        type="button"
                        class="absolute left-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur transition hover:bg-black/55"
                        @click="prev()"
                        x-show="imgs.length > 1"
                        aria-label="Eelmine pilt"
                    >‹</button>

                    <button
                        type="button"
                        class="absolute right-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur transition hover:bg-black/55"
                        @click="next()"
                        x-show="imgs.length > 1"
                        aria-label="Järgmine pilt"
                    >›</button>

                    <div
                        class="absolute bottom-3 left-3 z-10 hidden items-center gap-2 rounded-full bg-black/35 px-3 py-1.5 text-xs text-white opacity-0 backdrop-blur transition group-hover:opacity-100 sm:flex"
                        x-show="imgs.length"
                    >
                        <span>🔍</span>
                        <span>{{ __('Kliki, et suurendada') }}</span>
                    </div>

                    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-100">
                        <img
                            class="h-[42vh] w-full cursor-zoom-in object-contain md:h-[52vh]"
                            :src="imgs[idx]"
                            alt=""
                            x-show="imgs.length"
                            @click="openZoom()"
                        >

                        <div
                            class="flex h-[42vh] w-full items-center justify-center text-sm text-zinc-500 md:h-[52vh]"
                            x-show="!imgs.length"
                        >
                            {{ __('Pilte pole lisatud') }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 sm:grid-cols-6" x-show="imgs.length > 1">
                    <template x-for="(src, i) in imgs" :key="src + '-' + i">
                        <button
                            type="button"
                            class="relative aspect-square overflow-hidden rounded-2xl border bg-zinc-50 transition hover:shadow-sm"
                            :class="i === idx
                                ? 'border-zinc-900/30 ring-2 ring-zinc-900/20'
                                : 'border-zinc-200'"
                            @click="idx = i"
                        >
                            <img :src="src" class="h-full w-full object-cover" alt="">
                        </button>
                    </template>
                </div>

                <div class="text-center text-xs text-zinc-500" x-show="imgs.length">
                    <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
                </div>
            </div>

            <div class="md:col-span-5">
                <div class="space-y-4 rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm md:sticky md:top-6 md:p-5">
                    <div class="space-y-1">
                        <div
                            class="break-words text-2xl font-semibold tracking-tight text-zinc-900 md:text-3xl"
                            x-text="title"
                        ></div>

                        <div class="break-words text-sm text-zinc-600">
                            <span x-text="category"></span>
                            <span class="mx-2">•</span>
                            <span x-text="location"></span>
                        </div>
                    </div>

                    <div class="flex items-end justify-between gap-3">
                        <div class="text-xs uppercase tracking-wide text-zinc-500">
                            {{ __('Hind') }}
                        </div>
                        <div class="text-lg font-semibold text-zinc-900" x-text="price"></div>
                    </div>

                    <div class="h-px bg-zinc-200/80"></div>

                    <div class="space-y-1">
                        <div class="text-sm font-medium text-zinc-800">
                            {{ __('Seisukord') }}
                        </div>
                        <div class="text-sm text-zinc-600" x-text="condition"></div>
                    </div>

                    <div class="space-y-2">
                        <div class="text-sm font-medium text-zinc-800">
                            {{ __('Tarneviis') }}
                        </div>

                        <div class="flex flex-wrap gap-2" x-show="delivery && delivery.length">
                            <template x-for="(lbl, i) in delivery" :key="lbl + '-' + i">
                                <span
                                    class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs text-zinc-700"
                                    x-text="lbl"
                                ></span>
                            </template>
                        </div>

                        <div class="text-sm text-zinc-500" x-show="!delivery || !delivery.length">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm md:p-6">
            <div class="mb-2 text-sm font-medium text-zinc-800">
                {{ __('Kirjeldus') }}
            </div>

            <div
                class="prose prose-zinc max-w-none whitespace-pre-line break-words"
                x-text="description"
            ></div>
        </div>
    </div>

    <div
        x-show="zoomOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[60]"
        aria-modal="true"
        role="dialog"
    >
        <div class="absolute inset-0 bg-black/80" @click="closeZoom()"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4 md:p-8">
            <div class="relative w-full max-w-6xl">
                <button
                    type="button"
                    class="absolute -top-12 right-0 rounded-xl bg-white/10 px-3 py-2 text-sm text-white backdrop-blur hover:bg-white/20 md:-top-14"
                    @click="closeZoom()"
                >
                    ✕ {{ __('Sulge') }}
                </button>

                <button
                    type="button"
                    class="absolute left-0 top-1/2 flex h-11 w-11 -translate-x-2 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur hover:bg-white/20 md:-translate-x-12"
                    @click="prev()"
                    x-show="imgs.length > 1"
                    aria-label="Eelmine pilt"
                >
                    ‹
                </button>

                <button
                    type="button"
                    class="absolute right-0 top-1/2 flex h-11 w-11 translate-x-2 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur hover:bg-white/20 md:translate-x-12"
                    @click="next()"
                    x-show="imgs.length > 1"
                    aria-label="Järgmine pilt"
                >
                    ›
                </button>

                <div class="overflow-hidden rounded-3xl border border-white/10 bg-black/20">
                    <img
                        :src="imgs[idx]"
                        class="h-[75vh] w-full select-none object-contain"
                        alt=""
                        draggable="false"
                    >
                </div>

                <div class="mt-3 text-center text-sm text-white/80" x-show="imgs.length">
                    <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
                    <span class="mx-2">•</span>
                    <span class="text-white/60">{{ __('ESC sulgeb') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>