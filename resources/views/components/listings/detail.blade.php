@props(['listing'])

@php
    $images = [];

    if ($listing->relationLoaded('images')) {
        $images = $listing->images
            ->map(function ($img) {
                return [
                    'full' => $img->url(),
                    'thumb' => $img->thumbUrl(),
                ];
            })
            ->filter(function ($img) {
                return !empty($img['full']);
            })
            ->values()
            ->all();
    }

    $title = $listing->title ?? '—';
    $desc = $listing->description ?? '—';
    $cat = $listing->category?->name_et ?? '—';
    $loc = $listing->location?->full_label_et ?? '—';
    $condLabel = method_exists($listing, 'conditionLabel') ? $listing->conditionLabel() : '—';
    $deliveryLabels = $listing->deliveryOptionsLabels() ?? [];
    $priceText = method_exists($listing, 'priceLabel') ? $listing->priceLabel() : 'Kokkuleppel';

    $deliveryHtml = '—';

    if (!empty($deliveryLabels)) {
        $parts = [];

        foreach ($deliveryLabels as $lbl) {
            $parts[] = '<span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs text-zinc-700">' . e($lbl) . '</span>';
        }

        $deliveryHtml = implode('', $parts);
    }

    $imagesJson = json_encode($images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

<div
    class="w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm"
    x-data='{
        idx: 0,
        imgs: {!! $imagesJson !!},
        zoomOpen: false,

        openZoom(i = null) {
            if (this.imgs.length === 0) return;
            if (i !== null) this.idx = i;
            this.zoomOpen = true;
            document.documentElement.classList.add("overflow-hidden");
        },

        closeZoom() {
            this.zoomOpen = false;
            document.documentElement.classList.remove("overflow-hidden");
        },

        next() {
            if (this.imgs.length === 0) return;
            this.idx = (this.idx + 1) % this.imgs.length;
        },

        prev() {
            if (this.imgs.length === 0) return;
            this.idx = (this.idx - 1 + this.imgs.length) % this.imgs.length;
        }
    }'
    x-on:keydown.window.escape='if (zoomOpen) closeZoom()'
>
    <div class="space-y-5 p-4 md:space-y-6 md:p-6">
        <div class="grid gap-5 md:grid-cols-12">
            <div class="space-y-3 md:col-span-7">
                <div class="group relative">
                    <div class="absolute right-3 top-3 z-20">
                        <livewire:listings.favorite-toggle :listing="$listing" />
                    </div>

                    <button
                        type="button"
                        class="absolute left-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur transition hover:bg-black/55"
                        x-on:click="prev()"
                        x-show="imgs.length > 1"
                        aria-label="Eelmine pilt"
                    >‹</button>

                    <button
                        type="button"
                        class="absolute right-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur transition hover:bg-black/55"
                        x-on:click="next()"
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
                            :src='imgs[idx] && imgs[idx].full ? imgs[idx].full : "/images/placeholder.png"'
                            alt=""
                            x-show="imgs.length"
                            x-on:click="openZoom()"
                            loading="lazy"
                            decoding="async"
                            x-on:error='$el.src = "/images/placeholder.png"'
                        >

                        <div
                            class="flex h-[42vh] w-full items-center justify-center bg-zinc-100 md:h-[52vh]"
                            x-show="!imgs.length"
                        >
                            <img
                                src="/images/placeholder.png"
                                alt="{{ __('Pilte pole lisatud') }}"
                                class="h-full w-full object-contain"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 sm:grid-cols-6" x-show="imgs.length > 1">
                    <template x-for="(img, i) in imgs" :key="(img.full || '') + '-' + i">
                        <button
                            type="button"
                            class="relative aspect-square overflow-hidden rounded-2xl border bg-zinc-50 transition hover:shadow-sm"
                            :class='i === idx ? "border-zinc-900/30 ring-2 ring-zinc-900/20" : "border-zinc-200"'
                            x-on:click="idx = i"
                        >
                            <img
                                :src='img.thumb ? img.thumb : "/images/placeholder_thumb.png"'
                                class="h-full w-full object-cover"
                                alt=""
                                loading="lazy"
                                decoding="async"
                                x-on:error='$el.src = "/images/placeholder_thumb.png"'
                            >
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
                        <div class="break-words text-2xl font-semibold tracking-tight text-zinc-900 md:text-3xl">
                            {{ $title }}
                        </div>

                        <div class="break-words text-sm text-zinc-600">
                            <span>{{ $cat }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $loc }}</span>
                        </div>
                    </div>

                    <div class="flex items-end justify-between gap-3">
                        <div class="text-xs uppercase tracking-wide text-zinc-500">
                            {{ __('Hind') }}
                        </div>
                        <div class="text-lg font-semibold text-zinc-900">
                            {{ $priceText }}
                        </div>
                    </div>

                    <div class="h-px bg-zinc-200/80"></div>

                    <div class="space-y-1">
                        <div class="text-sm font-medium text-zinc-800">
                            {{ __('Seisukord') }}
                        </div>
                        <div class="text-sm text-zinc-600">
                            {{ $condLabel }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="text-sm font-medium text-zinc-800">
                            {{ __('Tarneviis') }}
                        </div>

                        <div class="flex flex-wrap gap-2">
                            {!! $deliveryHtml !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm md:p-6">
            <div class="mb-2 text-sm font-medium text-zinc-800">
                {{ __('Kirjeldus') }}
            </div>

            <div class="prose prose-zinc max-w-none whitespace-pre-line break-words">
                {{ $desc }}
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-show="zoomOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[9999]"
            aria-modal="true"
            role="dialog"
            style="display: none;"
        >
            <div class="absolute inset-0 bg-black/85 backdrop-blur-sm" x-on:click="closeZoom()"></div>

            <div class="absolute inset-0 flex items-center justify-center p-4 md:p-8">
                <div class="relative flex h-full w-full max-w-7xl items-center justify-center">
                    <button
                        type="button"
                        class="absolute right-0 top-0 z-20 rounded-2xl bg-white/10 px-4 py-2 text-sm font-bold text-white backdrop-blur transition hover:bg-white/20"
                        x-on:click="closeZoom()"
                    >
                        ✕ {{ __('Sulge') }}
                    </button>

                    <button
                        type="button"
                        class="absolute left-0 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-3xl text-white backdrop-blur transition hover:bg-white/20 md:left-4"
                        x-on:click.stop="prev()"
                        x-show="imgs.length > 1"
                        aria-label="Eelmine pilt"
                    >
                        ‹
                    </button>

                    <button
                        type="button"
                        class="absolute right-0 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-3xl text-white backdrop-blur transition hover:bg-white/20 md:right-4"
                        x-on:click.stop="next()"
                        x-show="imgs.length > 1"
                        aria-label="Järgmine pilt"
                    >
                        ›
                    </button>

                    <img
                        :src='imgs[idx] && imgs[idx].full ? imgs[idx].full : "/images/placeholder.png"'
                        class="max-h-[88vh] max-w-full select-none rounded-3xl object-contain shadow-2xl"
                        alt=""
                        draggable="false"
                        loading="lazy"
                        decoding="async"
                        x-on:error='$el.src = "/images/placeholder.png"'
                    >

                    <div class="absolute bottom-0 left-1/2 z-20 -translate-x-1/2 rounded-full bg-black/35 px-4 py-2 text-center text-sm text-white/80 backdrop-blur" x-show="imgs.length">
                        <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
                        <span class="mx-2">•</span>
                        <span class="text-white/60">{{ __('ESC sulgeb') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>