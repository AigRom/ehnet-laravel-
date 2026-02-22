{{-- resources/views/components/listings/detail.blade.php --}}

@props([
    /**
     * Listing mudel (DB vaates olemas, preview vaates võib olla null)
     */
    'listing' => null,

    /**
     * Piltide URL-id (DB vaates antakse sisse controllerist)
     * Preview vaates tulevad pildid JS eventiga.
     */
    'imageUrls' => [],

    /**
     * "db"     = näitab päris andmeid DB-st
     * "preview"= näitab eelvaadet (andmed tulevad JS eventiga)
     */
    'mode' => 'db', // db | preview
])

@php
    // Režiimid
    $isPreview = $mode === 'preview';

    // Filtreeri tühjad URL-id välja ja indeksid 0..n
    $imageUrls = array_values(array_filter($imageUrls));

    // Null-safe andmed (DB init väärtused)
    $title = $listing?->title ?? '—';
    $desc  = $listing?->description ?? '—';
    $cat   = $listing?->category?->name_et ?? '—';
    $loc   = $listing?->location?->full_label_et ?? '—';

    // Seisukord / tarne (DB init väärtused)
    $condLabel = ($listing && method_exists($listing, 'conditionLabel')) ? $listing->conditionLabel() : '—';
    $deliveryLabels = $listing?->deliveryOptionsLabels() ?? [];

    // Staatus (DB vaates)
    $statusLabel = ($listing && method_exists($listing, 'statusLabel')) ? $listing->statusLabel() : '—';
@endphp

<div
    class="w-full max-w-4xl rounded-2xl bg-white dark:bg-zinc-900 shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden"

    {{-- Alpine state: üks ühtne andmemudel nii DB kui preview jaoks --}}
    x-data="{
        // -------------------------
        // GALERII (pildid)
        // -------------------------
        idx: 0,
        imgs: @js($isPreview ? [] : $imageUrls),

        // -------------------------
        // SISU (tekst)
        // DB režiimis init PHP-st
        // Preview režiimis init placeholder ja täidetakse eventiga
        // -------------------------
        title: @js($isPreview ? '—' : $title),
        category: @js($isPreview ? '—' : $cat),
        location: @js($isPreview ? '—' : $loc),
        description: @js($isPreview ? '—' : $desc),

        // -------------------------
        // LISAVÄLJAD
        // -------------------------
        condition: @js($isPreview ? '—' : $condLabel),
        delivery: @js($isPreview ? [] : $deliveryLabels),

        // Hind on optional (kui detailis kuskil kuvad/hiljem lisad)
        price: @js(''),
    }"

    {{-- Preview sisu update: JS saadab ühe paketiga kõik väljad --}}
    @listing-preview-update.window="
        title = $event.detail?.title ?? title;
        category = $event.detail?.category ?? category;
        location = $event.detail?.location ?? location;
        description = $event.detail?.description ?? description;
        condition = $event.detail?.condition ?? condition;
        delivery = $event.detail?.delivery ?? delivery;
        price = $event.detail?.price ?? price;
    "

    {{-- Preview pildid: JS saadab piltide URL-id (base64 või blob url) --}}
    @listing-preview-images.window="
        imgs = ($event.detail?.images || []);
        idx = 0;
    "
>
    <div class="p-4 space-y-4">

        {{-- =========================
             PILDI GALERII (SUUR PILT)
             ========================= --}}
        <div class="relative">

            {{-- Vasak nool (ainult kui >1 pilt) --}}
            <button
                type="button"
                class="absolute left-2 top-1/2 -translate-y-1/2 z-10 text-white w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 flex items-center justify-center"
                @click="idx = (idx - 1 + imgs.length) % imgs.length"
                x-show="imgs.length > 1"
            >‹</button>

            {{-- Parem nool (ainult kui >1 pilt) --}}
            <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 z-10 text-white w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 flex items-center justify-center"
                @click="idx = (idx + 1) % imgs.length"
                x-show="imgs.length > 1"
            >›</button>

            {{-- Suur pilt / placeholder --}}
            <div class="rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <img
                    class="w-full h-[45vh] object-contain"
                    :src="imgs[idx]"
                    alt=""
                    x-show="imgs.length"
                >

                {{-- Kui pilte pole --}}
                <div class="w-full h-[45vh] flex items-center justify-center text-sm text-zinc-500"
                     x-show="!imgs.length">
                    {{ __('Pilte pole lisatud') }}
                </div>
            </div>

            {{-- Loendur (nt 2 / 5) --}}
            <div class="mt-2 text-center text-xs text-zinc-500" x-show="imgs.length">
                <span x-text="(idx + 1) + ' / ' + imgs.length"></span>
            </div>
        </div>

        {{-- =========================
             THUMBNAILID (VÄIKESED)
             Mobiilis vähem veerge
             ========================= --}}
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2" x-show="imgs.length > 1">
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

        {{-- =========================
             KUULUTUSE SISU (TEKST)
             Responsive: mobiilis üks veerg,
             desktopis kaks veergu (info + parempaneel)
             ========================= --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 space-y-4">

            {{-- Ülemine rida: vasakul title/meta, paremal seisukord+tarne --}}
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                {{-- VASAK: pealkiri + kategooria/asukoht + staatus --}}
                <div class="space-y-1 min-w-0 flex-1">

                    {{-- Pealkiri: break-words, et pikk tekst ei lõhuks layouti --}}
                    <div class="text-xl font-semibold break-words" x-text="title"></div>

                    {{-- Kategooria • asukoht --}}
                    <div class="text-sm text-zinc-600 dark:text-zinc-300 break-words">
                        <span x-text="category"></span>
                        <span class="mx-2">•</span>
                        <span x-text="location"></span>
                    </div>

                    {{-- Staatus: ainult DB vaates (preview’s tavaliselt pole vaja) --}}
                    @unless($isPreview)
                        <div class="text-xs text-zinc-500">
                            {{ __('Staatus:') }} {{ $statusLabel }}
                        </div>
                    @endunless
                </div>

                {{-- PAREM: Seisukord + tarneviis (desktopis paremal kastis, mobiilis all) --}}
                <div class="w-full md:w-72 md:flex-shrink-0 rounded-2xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800 p-3 space-y-4">

                    {{-- Seisukord --}}
                    <div>
                        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('Seisukord') }}
                        </div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300" x-text="condition"></div>
                    </div>

                    {{-- Tarneviis --}}
                    <div>
                        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ __('Tarneviis') }}
                        </div>

                        {{-- Kui tarnevalikuid on, näita badge’id --}}
                        <div class="mt-2 flex flex-wrap gap-2" x-show="delivery && delivery.length">
                            <template x-for="(lbl, i) in delivery" :key="lbl + '-' + i">
                                <span
                                    class="text-xs px-2 py-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900"
                                    x-text="lbl"
                                ></span>
                            </template>
                        </div>

                        {{-- Kui tarnevalikuid pole --}}
                        <div class="mt-2 text-sm text-zinc-500" x-show="!delivery || !delivery.length">—</div>
                    </div>

                </div>
            </div>

            {{-- Kirjeldus: break-words + whitespace-pre-line, et pikk tekst ei kattuks --}}
            <div class="prose prose-zinc dark:prose-invert max-w-none whitespace-pre-line break-words" x-text="description"></div>

        </div>

    </div>
</div>