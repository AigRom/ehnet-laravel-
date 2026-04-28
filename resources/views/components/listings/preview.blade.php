<div
    id="listingPreviewModal"
    class="fixed inset-0 hidden z-50 bg-black/70"
    aria-hidden="true"
    x-cloak
    x-data="{
        title: '—',
        category: '—',
        location: '—',
        description: '—',
        condition: '—',
        delivery: [],
        price: 'Kokkuleppel',
        vatText: '',
        images: [],
        idx: 0,

        next() {
            if (this.images.length === 0) return;
            this.idx = (this.idx + 1) % this.images.length;
        },

        prev() {
            if (this.images.length === 0) return;
            this.idx = (this.idx - 1 + this.images.length) % this.images.length;
        }
    }"
    x-on:listing-preview-update.window="
        title = $event.detail?.title ?? '—';
        category = $event.detail?.category ?? '—';
        location = $event.detail?.location ?? '—';
        description = $event.detail?.description ?? '—';
        condition = $event.detail?.condition ?? '—';
        delivery = Array.isArray($event.detail?.delivery) ? $event.detail.delivery : [];
        price = $event.detail?.price ?? 'Kokkuleppel';
        vatText = $event.detail?.vatText ?? $event.detail?.vat_text ?? '';
    "
    x-on:listing-preview-images.window="
        images = Array.isArray($event.detail?.images) ? $event.detail.images : [];
        idx = 0;
    "
>
    <div class="flex min-h-full w-full items-start justify-center overflow-y-auto p-3 sm:items-center sm:p-6">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl">

            {{-- HEADER --}}
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-white/90 p-4 backdrop-blur">
                <div class="text-base font-semibold text-zinc-900">
                    {{ __('Kuulutuse eelvaade') }}
                </div>

                <button
                    type="button"
                    id="closeListingPreview"
                    class="rounded-lg bg-zinc-100 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-200"
                >
                    {{ __('Sulge') }}
                </button>
            </div>

            {{-- CONTENT --}}
            <div class="p-4 md:p-6">
                <div class="space-y-5">

                    {{-- GRID --}}
                    <div class="grid gap-5 md:grid-cols-12">

                        {{-- IMAGE --}}
                        <div class="space-y-3 md:col-span-7">
                            <div class="relative">

                                <button
                                    type="button"
                                    class="absolute left-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white hover:bg-black/55"
                                    x-on:click="prev()"
                                    x-show="images.length > 1"
                                >
                                    ‹
                                </button>

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white hover:bg-black/55"
                                    x-on:click="next()"
                                    x-show="images.length > 1"
                                >
                                    ›
                                </button>

                                <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-100">
                                    <img
                                        class="h-[42vh] w-full object-contain md:h-[52vh]"
                                        :src="images[idx] || '/images/placeholder.png'"
                                        x-on:error="$el.src = '/images/placeholder.png'"
                                    >
                                </div>
                            </div>

                            {{-- THUMBS --}}
                            <div class="grid grid-cols-4 gap-2 sm:grid-cols-6" x-show="images.length > 1">
                                <template x-for="(src, i) in images" :key="i">
                                    <button
                                        type="button"
                                        class="aspect-square overflow-hidden rounded-2xl border bg-zinc-50"
                                        :class="i === idx
                                            ? 'ring-2 ring-zinc-400'
                                            : 'border-zinc-200'"
                                        x-on:click="idx = i"
                                    >
                                        <img
                                            :src="src || '/images/placeholder_thumb.png'"
                                            class="h-full w-full object-cover"
                                            x-on:error="$el.src = '/images/placeholder_thumb.png'"
                                        >
                                    </button>
                                </template>
                            </div>

                            <div class="text-center text-xs text-zinc-500" x-show="images.length > 0">
                                <span x-text="(idx + 1) + ' / ' + images.length"></span>
                            </div>
                        </div>

                        {{-- INFO --}}
                        <div class="md:col-span-5">
                            <div class="space-y-4 rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">

                                <div>
                                    <div class="text-2xl font-semibold text-zinc-900 md:text-3xl" x-text="title"></div>

                                    <div class="text-sm text-zinc-600">
                                        <span x-text="category"></span>
                                        <span class="mx-2">•</span>
                                        <span x-text="location"></span>
                                    </div>
                                </div>

                                <div class="flex items-start justify-between gap-4">
                                    <div class="text-xs uppercase text-zinc-500">
                                        {{ __('Hind') }}
                                    </div>

                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-zinc-900" x-text="price"></div>

                                        <div
                                            x-show="vatText"
                                            class="mt-1 text-xs font-medium text-zinc-500"
                                            x-text="vatText"
                                        ></div>
                                    </div>
                                </div>

                                <div class="h-px bg-zinc-200"></div>

                                <div>
                                    <div class="text-sm font-medium text-zinc-800">
                                        {{ __('Seisukord') }}
                                    </div>
                                    <div class="text-sm text-zinc-600" x-text="condition"></div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-zinc-800">
                                        {{ __('Tarneviis') }}
                                    </div>

                                    <div class="mt-1 flex flex-wrap gap-2" x-show="delivery.length > 0">
                                        <template x-for="(lbl, i) in delivery" :key="i">
                                            <span
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs text-zinc-700"
                                                x-text="lbl"
                                            ></span>
                                        </template>
                                    </div>

                                    <div class="text-sm text-zinc-500" x-show="delivery.length === 0">
                                        —
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="mb-2 text-sm font-medium text-zinc-800">
                            {{ __('Kirjeldus') }}
                        </div>

                        <div class="whitespace-pre-line text-zinc-700" x-text="description"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>