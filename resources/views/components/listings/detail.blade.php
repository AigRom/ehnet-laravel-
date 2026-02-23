{{-- resources/views/components/listings/detail.blade.php --}}
{{-- 
    EHNET: Kuulutuse detail-komponent (moodsam + pildi zoom modal)

    Uus:
    - Klikk suurele pildile avab zoom-modali
    - Modalis: next/prev, sulge (X), ESC, klikk taustale sulgeb
    - Veidi “Airbnb-likum” visuaal: pehmemad varjud, parem spacing, sticky parempaneel desktopis
--}}

@props([
    'listing' => null,
    'imageUrls' => [],
    'mode' => 'db', // db | preview
])

@php
    use Illuminate\Support\Facades\Storage;

    // -------------------------
    // 1) Režiimid
    // -------------------------
    $isPreview = $mode === 'preview';

    // -------------------------
    // 2) Pildid (DB fallback)
    // -------------------------
    if (!$isPreview && empty($imageUrls) && $listing && $listing->relationLoaded('images')) {
        $imageUrls = $listing->images
            ->map(fn ($img) => $img->path ? Storage::url($img->path) : null)
            ->filter()
            ->values()
            ->all();
    }

    $imageUrls = array_values(array_filter($imageUrls));

    // -------------------------
    // 3) Tekstiväljad (DB init)
    // -------------------------
    $title = $listing?->title ?? '—';
    $desc  = $listing?->description ?? '—';
    $cat   = $listing?->category?->name_et ?? '—';
    $loc   = $listing?->location?->full_label_et ?? '—';

    // -------------------------
    // 4) Lisaväljad (DB init)
    // -------------------------
    $condLabel = ($listing && method_exists($listing, 'conditionLabel')) ? $listing->conditionLabel() : '—';
    $deliveryLabels = $listing?->deliveryOptionsLabels() ?? [];

    // -------------------------
    // 5) Hind (DB init) – sama loogika nagu card.blade.php
    // -------------------------
    $priceText = 'Kokkuleppel';
    if ($listing && $listing->price !== null) {
        $priceText = ((float) $listing->price == 0.0)
            ? 'Tasuta'
            : rtrim(rtrim(number_format((float) $listing->price, 2, '.', ''), '0'), '.') . ' ' . ($listing->currency ?? 'EUR');
    }
@endphp

<div
    class="w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200/80 bg-white shadow-sm
           dark:border-zinc-800 dark:bg-zinc-900"

    x-data="{
        // -------------------------
        // GALERII
        // -------------------------
        idx: 0,
        imgs: @js($isPreview ? [] : $imageUrls),

        // Zoom modal
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

        // -------------------------
        // SISU (DB init / preview)
        // -------------------------
        title: @js($isPreview ? '—' : $title),
        category: @js($isPreview ? '—' : $cat),
        location: @js($isPreview ? '—' : $loc),
        description: @js($isPreview ? '—' : $desc),

        condition: @js($isPreview ? '—' : $condLabel),
        delivery: @js($isPreview ? [] : $deliveryLabels),
        price: @js($isPreview ? 'Kokkuleppel' : $priceText),
    }"

    {{-- Preview sisu update --}}
    @listing-preview-update.window="
        title = $event.detail?.title ?? title;
        category = $event.detail?.category ?? category;
        location = $event.detail?.location ?? location;
        description = $event.detail?.description ?? description;
        condition = $event.detail?.condition ?? condition;
        delivery = $event.detail?.delivery ?? delivery;
        price = $event.detail?.price ?? price;
    "

    {{-- Preview pildid --}}
    @listing-preview-images.window="
        imgs = ($event.detail?.images || []);
        idx = 0;
    "

    {{-- ESC sulgeb zoomi --}}
    @keydown.window.escape="if (zoomOpen) closeZoom()"
>
    <div class="p-4 md:p-6 space-y-5 md:space-y-6">

        {{-- ==========================================================
            ÜLEMINE: GALERII + INFO (mobiilis stacked, desktopis grid)
           ========================================================== --}}
        <div class="grid gap-5 md:grid-cols-12">

            {{-- --------------------------
                GALERII (vasak / üleval)
               -------------------------- --}}
            <div class="md:col-span-7 space-y-3">

                {{-- SUUR PILT --}}
                <div class="relative group">
                    {{-- nav nooled --}}
                    <button
                        type="button"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10
                               text-white w-10 h-10 rounded-full bg-black/35 hover:bg-black/55
                               flex items-center justify-center backdrop-blur
                               transition"
                        @click="prev()"
                        x-show="imgs.length > 1"
                        aria-label="Eelmine pilt"
                    >‹</button>

                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10
                               text-white w-10 h-10 rounded-full bg-black/35 hover:bg-black/55
                               flex items-center justify-center backdrop-blur
                               transition"
                        @click="next()"
                        x-show="imgs.length > 1"
                        aria-label="Järgmine pilt"
                    >›</button>

                    {{-- “Klikk zoomiks” hint --}}
                    <div
                        class="absolute bottom-3 left-3 z-10 hidden sm:flex items-center gap-2
                               rounded-full bg-black/35 text-white text-xs px-3 py-1.5
                               backdrop-blur opacity-0 group-hover:opacity-100 transition"
                        x-show="imgs.length"
                    >
                        <span class="inline-block">🔍</span>
                        <span>{{ __('Kliki, et suurendada') }}</span>
                    </div>

                    {{-- pilt/placeholder --}}
                    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-100
                                dark:border-zinc-800 dark:bg-zinc-800">
                        <img
                            class="w-full h-[42vh] md:h-[52vh] object-contain cursor-zoom-in"
                            :src="imgs[idx]"
                            alt=""
                            x-show="imgs.length"
                            @click="openZoom()"
                        >

                        <div
                            class="w-full h-[42vh] md:h-[52vh] flex items-center justify-center text-sm text-zinc-500"
                            x-show="!imgs.length"
                        >
                            {{ __('Pilte pole lisatud') }}
                        </div>
                    </div>
                </div>

                {{-- THUMBNAILID --}}
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2" x-show="imgs.length > 1">
                    <template x-for="(src, i) in imgs" :key="src + '-' + i">
                        <button
                            type="button"
                            class="relative aspect-square overflow-hidden rounded-2xl border bg-zinc-50
                                   dark:bg-zinc-900 transition
                                   hover:shadow-sm"
                            :class="i === idx
                                ? 'border-zinc-900/30 ring-2 ring-zinc-900/20 dark:border-white/20 dark:ring-white/20'
                                : 'border-zinc-200 dark:border-zinc-800'"
                            @click="idx = i"
                        >
                            <img :src="src" class="h-full w-full object-cover" alt="">
                        </button>
                    </template>
                </div>

                {{-- loendur --}}
                <div class="text-center text-xs text-zinc-500" x-show="imgs.length">
                    <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
                </div>
            </div>

            {{-- --------------------------
                INFO (parem / all)
               -------------------------- --}}
            <div class="md:col-span-5">
                <div class="rounded-3xl border border-zinc-200 bg-white p-4 md:p-5 shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900
                            md:sticky md:top-6 space-y-4">

                    {{-- Pealkiri --}}
                    <div class="space-y-1">
                        <div class="text-2xl md:text-3xl font-semibold tracking-tight break-words text-zinc-900 dark:text-white"
                             x-text="title"></div>

                        {{-- Kategooria • asukoht --}}
                        <div class="text-sm text-zinc-600 dark:text-zinc-300 break-words">
                            <span x-text="category"></span>
                            <span class="mx-2">•</span>
                            <span x-text="location"></span>
                        </div>
                    </div>

                    {{-- Hind (paremas kastis, “moodsalt” esile) --}}
                    <div class="flex items-end justify-between gap-3">
                        <div class="text-xs uppercase tracking-wide text-zinc-500">
                            {{ __('Hind') }}
                        </div>
                        <div class="text-lg font-semibold text-zinc-900 dark:text-white" x-text="price"></div>
                    </div>

                    <div class="h-px bg-zinc-200/80 dark:bg-zinc-800"></div>

                    {{-- Seisukord --}}
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('Seisukord') }}
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-300" x-text="condition"></div>
                    </div>

                    {{-- Tarne --}}
                    <div class="space-y-2">
                        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('Tarneviis') }}
                        </div>

                        <div class="flex flex-wrap gap-2" x-show="delivery && delivery.length">
                            <template x-for="(lbl, i) in delivery" :key="lbl + '-' + i">
                                <span
                                    class="text-xs px-3 py-1.5 rounded-full
                                           border border-zinc-200 bg-zinc-50 text-zinc-700
                                           dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200"
                                    x-text="lbl"
                                ></span>
                            </template>
                        </div>

                        <div class="text-sm text-zinc-500" x-show="!delivery || !delivery.length">—</div>
                    </div>

                    {{-- (Siia saad hiljem lisada CTA: “Võta ühendust”) --}}
                </div>
            </div>
        </div>

        {{-- ==========================================================
            KIRJELDUS
           ========================================================== --}}
        <div class="rounded-3xl border border-zinc-200 bg-white p-4 md:p-6 shadow-sm
                    dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                {{ __('Kirjeldus') }}
            </div>

            <div class="prose prose-zinc dark:prose-invert max-w-none whitespace-pre-line break-words"
                 x-text="description"></div>
        </div>
    </div>

    {{-- ==========================================================
        ZOOM MODAL (toimiv)
    ========================================================== --}}
    <div
        x-show="zoomOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[60]"
        aria-modal="true"
        role="dialog"
    >
        {{-- taust --}}
        <div class="absolute inset-0 bg-black/80" @click="closeZoom()"></div>

        {{-- sisu --}}
        <div class="absolute inset-0 p-4 md:p-8 flex items-center justify-center">
            <div class="relative w-full max-w-6xl">

                {{-- sulge --}}
                <button
                    type="button"
                    class="absolute -top-12 right-0 md:-top-14
                        text-white text-sm px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur"
                    @click="closeZoom()"
                >
                    ✕ {{ __('Sulge') }}
                </button>

                {{-- nav nooled --}}
                <button
                    type="button"
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 md:-translate-x-12
                        text-white w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur
                        flex items-center justify-center"
                    @click="prev()"
                    x-show="imgs.length > 1"
                    aria-label="Eelmine pilt"
                >
                    ‹
                </button>

                <button
                    type="button"
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 md:translate-x-12
                        text-white w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur
                        flex items-center justify-center"
                    @click="next()"
                    x-show="imgs.length > 1"
                    aria-label="Järgmine pilt"
                >
                    ›
                </button>

                {{-- pilt --}}
                <div class="overflow-hidden rounded-3xl border border-white/10 bg-black/20">
                    <img
                        :src="imgs[idx]"
                        class="w-full h-[75vh] object-contain select-none"
                        alt=""
                        draggable="false"
                    >
                </div>

                {{-- loendur --}}
                <div class="mt-3 text-center text-sm text-white/80" x-show="imgs.length">
                    <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
                    <span class="mx-2">•</span>
                    <span class="text-white/60">{{ __('ESC sulgeb') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>