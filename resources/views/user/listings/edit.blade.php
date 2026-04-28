@php
    $existingImages = $listing->images
        ->sortBy('sort_order')
        ->values()
        ->map(fn ($img) => [
            'id' => $img->id,
            'src' => $img->url(),
            'thumb' => $img->thumbUrl(),
            'name' => basename($img->path),
        ]);

    $isBusinessAccount = auth()->user()?->type === 'business'
        || filled(auth()->user()?->company_name);

    $userLocationId = auth()->user()->location_id ?? null;
    $userLocationLabel = auth()->user()->location?->full_label_et ?? null;

    $currentLocationId = old('location_id', $listing->location_id);
    $currentLocationLabel = old('location_label', $listing->location?->full_label_et ?? '');

    $delivery = old('delivery_options', $listing->delivery_options ?? []);
    if (! is_array($delivery)) {
        $delivery = [];
    }

    $cond = old('condition', $listing->condition);

    $oldPrice = old('price', $listing->price);

    $priceMode = old(
        'price_mode',
        ($oldPrice === '0' || $oldPrice === 0 || (is_numeric($oldPrice) && (float) $oldPrice === 0.0))
            ? 'free'
            : (($oldPrice === null || $oldPrice === '') ? 'deal' : 'price')
    );
@endphp

<x-layouts.app.public :title="__('Muuda kuulutust')">
    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Header --}}
        <div class="rounded-[2rem] border border-emerald-950/10 bg-white/85 p-6 shadow-xl shadow-emerald-950/5 backdrop-blur sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="mb-3 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-900 ring-1 ring-emerald-900/10">
                        {{ __('Kuulutuse muutmine') }}
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 sm:text-4xl">
                        {{ __('Muuda kuulutust') }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-base font-medium leading-7 text-zinc-600">
                        {{ __('Uuenda pildid ja põhiinfo. Muudatused salvestuvad kohe kuulutusele.') }}
                    </p>
                </div>

                <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 ring-1 ring-emerald-900/10">
                    {{ __('Kuni 10 pilti') }}
                </div>
            </div>
        </div>

        <form
            id="listingEditForm"
            method="POST"
            action="{{ route('listings.mine.update', $listing) }}"
            class="space-y-6"
            enctype="multipart/form-data"
            novalidate
        >
            @csrf
            @method('PATCH')

            <input
                type="hidden"
                name="action"
                id="formAction"
                value="{{ $listing->status === 'draft' ? 'draft' : 'publish' }}"
            >

            {{-- Card: Pildid --}}
            <div
                x-data="listingImagesEdit({
                    existing: @js($existingImages),
                    maxImages: 10
                })"
                class="space-y-4 rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-xl shadow-emerald-950/5 sm:p-6"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-emerald-950">
                            {{ __('Pildid') }}
                        </h2>

                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-500">
                            {{ __('Kokku kuni 10 pilti. Esimene pilt on kuulutuse kaanepilt.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-stone-50 px-3 py-2 text-xs font-bold text-zinc-500 ring-1 ring-emerald-950/10">
                        {{ __('Järjekorda saad muuta pildi all olevate nuppudega') }}
                    </div>
                </div>

                <input
                    x-ref="input"
                    id="images"
                    type="file"
                    name="new_images[]"
                    multiple
                    accept="image/*"
                    class="hidden"
                    @change="handleFiles($event)"
                >

                <input type="hidden" name="images_order" id="images_order" x-model="imagesOrderJson">
                <input type="hidden" name="deleted_image_ids" id="deleted_image_ids" x-model="deletedImageIdsJson">

                @error('new_images')
                    <p class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                        {{ $message }}
                    </p>
                @enderror

                @error('new_images.*')
                    <p class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                        {{ $message }}
                    </p>
                @enderror

                <div data-listing-images-grid class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    <template x-for="(item, index) in visibleItems()" :key="item.uid">
                        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-emerald-950/10">
                            <div class="group relative aspect-square bg-stone-100">
                                <img
                                    :src="item.preview"
                                    :alt="item.name || ''"
                                    class="h-full w-full cursor-zoom-in object-cover transition duration-300 group-hover:scale-[1.03]"
                                    @click="openModal(index)"
                                >

                                <div class="absolute bottom-2 left-2 rounded-xl bg-emerald-950/85 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm backdrop-blur">
                                    <template x-if="index === 0">
                                        <span>{{ __('Kaanepilt') }}</span>
                                    </template>

                                    <template x-if="index !== 0">
                                        <span x-text="`#${index + 1}`"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 border-t border-emerald-950/10 bg-white p-2">
                                <button
                                    type="button"
                                    class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-emerald-950/10 bg-stone-50 px-2 text-emerald-950 transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="moveUp(index)"
                                    :disabled="index === 0"
                                    title="{{ __('Liiguta ettepoole') }}"
                                    aria-label="{{ __('Liiguta ettepoole') }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 3.25a.75.75 0 0 1 .53.22l4.25 4.25a.75.75 0 0 1-1.06 1.06L10.75 5.81V16a.75.75 0 0 1-1.5 0V5.81L6.28 8.78a.75.75 0 0 1-1.06-1.06l4.25-4.25a.75.75 0 0 1 .53-.22Z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-emerald-950/10 bg-stone-50 px-2 text-emerald-950 transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="moveDown(index)"
                                    :disabled="index === visibleItems().length - 1"
                                    title="{{ __('Liiguta tahapoole') }}"
                                    aria-label="{{ __('Liiguta tahapoole') }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 16.75a.75.75 0 0 1-.53-.22l-4.25-4.25a.75.75 0 1 1 1.06-1.06l2.97 2.97V4a.75.75 0 0 1 1.5 0v10.19l2.97-2.97a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-.53.22Z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-2 text-red-700 transition hover:bg-red-100"
                                    @click="remove(index)"
                                    title="{{ __('Eemalda') }}"
                                    aria-label="{{ __('Eemalda') }}"
                                >
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <button
                        x-show="visibleItems().length < maxImages"
                        type="button"
                        @click="$refs.input.value = null; $refs.input.click()"
                        class="flex aspect-square items-center justify-center rounded-2xl border-2 border-dashed border-emerald-900/20 bg-emerald-50/40 text-emerald-900 transition hover:border-emerald-900/35 hover:bg-emerald-50 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                    >
                        <div class="flex flex-col items-center gap-2">
                            <svg class="h-9 w-9" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 4a.75.75 0 0 0-1.5 0v5.25H4a.75.75 0 0 0 0 1.5h5.25V16a.75.75 0 0 0 1.5 0v-5.25H16a.75.75 0 0 0 0-1.5h-5.25V4Z" />
                            </svg>

                            <span class="text-sm font-extrabold">
                                {{ __('Lisa pilt') }}
                            </span>
                        </div>
                    </button>
                </div>

                <div
                    x-cloak
                    x-show="modalOpen"
                    x-transition.opacity
                    @keydown.window.escape="closeModal()"
                    @keydown.window.arrow-left="if (modalOpen) prevModal()"
                    @keydown.window.arrow-right="if (modalOpen) nextModal()"
                    @click.self="closeModal()"
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/85 p-4 backdrop-blur-sm"
                    style="display: none;"
                >
                    <div class="relative w-full max-w-5xl">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="absolute -top-14 right-0 inline-flex items-center justify-center rounded-2xl bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20 focus:outline-none focus:ring-4 focus:ring-white/20"
                        >
                            {{ __('Sulge') }}
                        </button>

                        <button
                            type="button"
                            @click="prevModal()"
                            class="absolute left-0 top-1/2 z-10 inline-flex h-11 w-11 -translate-x-2 -translate-y-1/2 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur transition hover:bg-white/20 md:-translate-x-14"
                            aria-label="{{ __('Eelmine pilt') }}"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L9.06 10l3.71 3.71a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.08.02Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="nextModal()"
                            class="absolute right-0 top-1/2 z-10 inline-flex h-11 w-11 translate-x-2 -translate-y-1/2 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur transition hover:bg-white/20 md:translate-x-14"
                            aria-label="{{ __('Järgmine pilt') }}"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.08-.02Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div class="overflow-hidden rounded-[2rem] bg-black/30 ring-1 ring-white/10">
                            <img :src="modalImageSrc()" class="h-[75vh] w-full object-contain" alt="">
                        </div>

                        <div class="mt-3 text-center text-sm font-semibold text-white/80">
                            <span x-text="modalCounterText()"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Põhiinfo --}}
            <div class="space-y-6 rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-xl shadow-emerald-950/5 sm:p-6">
                <div>
                    <h2 class="text-xl font-extrabold text-emerald-950">
                        {{ __('Põhiinfo') }}
                    </h2>

                    <p class="mt-1 text-sm font-medium text-zinc-500">
                        {{ __('Täida kuulutuse peamised andmed võimalikult selgelt.') }}
                    </p>
                </div>

                <div>
                    <label for="title" class="mb-2 block text-sm font-bold text-emerald-950">
                        {{ __('Pealkiri') }}
                    </label>

                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $listing->title) }}"
                        maxlength="140"
                        autocomplete="off"
                        placeholder="{{ __('Nt. Kipsplaatide jäägid') }}"
                        @class([
                            'block w-full rounded-2xl bg-stone-50 px-4 py-3.5 text-base font-medium text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:bg-white',
                            'border border-emerald-950/10 focus:border-emerald-900/30 focus:ring-4 focus:ring-emerald-900/10' => !$errors->has('title'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('title'),
                        ])
                    >

                    @error('title')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-emerald-950">
                        {{ __('Seisukord') }}
                        <span class="font-medium text-zinc-500">{{ __('(valikuline)') }}</span>
                    </label>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 transition hover:bg-emerald-50/50">
                            <input type="radio" name="condition" value="new" @checked($cond === 'new') class="h-4 w-4 accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Uus') }}</span>
                        </label>

                        <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 transition hover:bg-emerald-50/50">
                            <input type="radio" name="condition" value="used" @checked($cond === 'used') class="h-4 w-4 accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Kasutatud') }}</span>
                        </label>

                        <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 transition hover:bg-emerald-50/50">
                            <input type="radio" name="condition" value="leftover" @checked($cond === 'leftover') class="h-4 w-4 accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Jääk') }}</span>
                        </label>
                    </div>

                    @error('condition')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="mb-2 block text-sm font-bold text-emerald-950">
                        {{ __('Kategooria') }}
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        @class([
                            'w-full rounded-2xl bg-stone-50 px-4 py-3.5 text-base font-medium text-emerald-950 outline-none transition focus:bg-white',
                            'border border-emerald-950/10 focus:border-emerald-900/30 focus:ring-4 focus:ring-emerald-900/10' => !$errors->has('category_id'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('category_id'),
                        ])
                    >
                        <option value="">{{ __('Vali kategooria') }}</option>

                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $listing->category_id) == $cat->id)>
                                {{ $cat->name_et }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div
                    class="relative overflow-visible"
                    x-data="{
                        myLocationId: {{ $userLocationId ? (int) $userLocationId : 'null' }},
                        myLocationLabel: @js($userLocationLabel),
                        useMyLocation() {
                            if (!this.myLocationId) return;
                            Livewire.dispatch('loc:set', { id: this.myLocationId });

                            const el = document.getElementById('location_label');
                            if (el) el.value = this.myLocationLabel || '';
                        }
                    }"
                    x-on:loc:selected.window="
                        const el = document.getElementById('location_label');
                        if (el && $event.detail && $event.detail.label !== undefined) {
                            el.value = $event.detail.label || '';
                        }
                    "
                    x-on:loc:clear.window="
                        const el = document.getElementById('location_label');
                        if (el) el.value = '';
                    "
                >
                    <livewire:location-autocomplete
                        :initial-id="$currentLocationId"
                        :wire:key="'loc-edit-'.$listing->id.'-'.($currentLocationId ?? 'new')"
                    />

                    <input
                        type="hidden"
                        name="location_label"
                        id="location_label"
                        value="{{ $currentLocationLabel }}"
                    >

                    @if($userLocationId)
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                            <span class="text-sm font-medium text-zinc-500">
                                {{ __('või') }}
                            </span>

                            <button
                                type="button"
                                x-on:click="useMyLocation()"
                                class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-4 py-2.5 text-sm font-bold text-emerald-950 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                            >
                                {{ __('Kasuta minu asukohta') }}
                            </button>
                        </div>
                    @endif

                    @error('location_id')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-emerald-950">
                        {{ __('Kättesaamine') }}
                        <span class="font-medium text-zinc-500">{{ __('(võid valida mitu)') }}</span>
                    </label>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 p-4 transition hover:bg-emerald-50/50">
                            <input type="checkbox" name="delivery_options[]" value="pickup" @checked(in_array('pickup', $delivery, true)) class="h-4 w-4 rounded accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Järeletulemine') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 p-4 transition hover:bg-emerald-50/50">
                            <input type="checkbox" name="delivery_options[]" value="seller_delivery" @checked(in_array('seller_delivery', $delivery, true)) class="h-4 w-4 rounded accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Transpordi võimalus') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 p-4 transition hover:bg-emerald-50/50">
                            <input type="checkbox" name="delivery_options[]" value="courier" @checked(in_array('courier', $delivery, true)) class="h-4 w-4 rounded accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Saadan kulleriga või pakiautomaati') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 p-4 transition hover:bg-emerald-50/50">
                            <input type="checkbox" name="delivery_options[]" value="agreement" @checked(in_array('agreement', $delivery, true)) class="h-4 w-4 rounded accent-emerald-900">
                            <span class="text-sm font-bold text-emerald-950">{{ __('Lepime kokku') }}</span>
                        </label>
                    </div>

                    @error('delivery_options')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror

                    @error('delivery_options.*')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div
                    x-data="{
                        mode: @js($priceMode),
                        syncPrice() {
                            const price = this.$refs.price;

                            if (!price) return;

                            if (this.mode === 'deal') {
                                price.value = '';
                            }

                            if (this.mode === 'free') {
                                price.value = '0';
                            }
                        }
                    }"
                    x-init="syncPrice()"
                >
                    <label class="mb-2 block text-sm font-bold text-emerald-950">
                        {{ __('Hind') }}
                    </label>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 transition hover:bg-emerald-50/50">
                            <input
                                type="radio"
                                name="price_mode"
                                value="price"
                                x-model="mode"
                                @change="syncPrice()"
                                class="h-4 w-4 accent-emerald-900"
                            >

                            <span class="text-sm font-bold text-emerald-950">
                                {{ __('Hind') }}
                            </span>
                        </label>

                        <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 transition hover:bg-emerald-50/50">
                            <input
                                type="radio"
                                name="price_mode"
                                value="free"
                                x-model="mode"
                                @change="syncPrice()"
                                class="h-4 w-4 accent-emerald-900"
                            >

                            <span class="text-sm font-bold text-emerald-950">
                                {{ __('Tasuta') }}
                            </span>
                        </label>

                        <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 transition hover:bg-emerald-50/50">
                            <input
                                type="radio"
                                name="price_mode"
                                value="deal"
                                x-model="mode"
                                @change="syncPrice()"
                                class="h-4 w-4 accent-emerald-900"
                            >

                            <span class="text-sm font-bold text-emerald-950">
                                {{ __('Kokkuleppel') }}
                            </span>
                        </label>

                        <div class="sm:col-span-3" x-show="mode === 'price'" x-transition>
                            <label for="price" class="mb-2 mt-3 block text-sm font-bold text-emerald-950">
                                {{ __('Summa (EUR)') }}
                            </label>

                            <input
                                x-ref="price"
                                id="price"
                                name="price"
                                type="text"
                                value="{{ old('price', $listing->price) }}"
                                inputmode="decimal"
                                autocomplete="off"
                                placeholder="{{ __('Nt. 25,00 või 25.00') }}"
                                @class([
                                    'block w-full rounded-2xl bg-stone-50 px-4 py-3.5 text-base font-medium text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:bg-white',
                                    'border border-emerald-950/10 focus:border-emerald-900/30 focus:ring-4 focus:ring-emerald-900/10' => !$errors->has('price'),
                                    'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('price'),
                                ])
                            >

                            @if($isBusinessAccount)
                                <label
                                    class="mt-3 flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-950/10 bg-emerald-50/40 px-4 py-3 transition hover:bg-emerald-50"
                                >
                                    <input
                                        type="checkbox"
                                        name="vat_included"
                                        value="1"
                                        @checked(old('vat_included', $listing->vat_included))
                                        x-bind:disabled="mode !== 'price'"
                                        class="mt-1 h-4 w-4 rounded accent-emerald-900"
                                    >

                                    <span>
                                        <span class="block text-sm font-bold text-emerald-950">
                                            {{ __('Hind sisaldab käibemaksu') }}
                                        </span>

                                        <span class="mt-0.5 block text-xs font-medium leading-5 text-zinc-500">
                                            {{ __('Märgi see, kui sisestatud hind on lõpphind koos käibemaksuga.') }}
                                        </span>
                                    </span>
                                </label>
                            @endif
                        </div>

                        <input type="hidden" name="price_normalized" x-bind:value="mode">
                    </div>

                    @error('price')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror

                    @error('price_mode')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror

                    @error('vat_included')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-bold text-emerald-950">
                        {{ __('Kirjeldus') }}
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        maxlength="5000"
                        rows="7"
                        placeholder="{{ __('Kirjelda kogust, mõõte, seisukorda ja kättesaamise tingimusi.') }}"
                        @class([
                            'block w-full resize-none rounded-2xl bg-stone-50 px-4 py-3.5 text-base font-medium leading-7 text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:bg-white',
                            'border border-emerald-950/10 focus:border-emerald-900/30 focus:ring-4 focus:ring-emerald-900/10' => !$errors->has('description'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('description'),
                        ])
                    >{{ old('description', $listing->description) }}</textarea>

                    @error('description')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-xl shadow-emerald-950/5 sm:p-6">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <a
                        href="{{ request('return_to', route('listings.mine', ['listing' => $listing->id])) }}"
                        wire:navigate
                        class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-5 py-3.5 text-base font-extrabold text-emerald-950 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                    >
                        {{ __('Tühista') }}
                    </a>

                    @if($listing->status === 'draft')
                        <button
                            type="submit"
                            id="saveDraftBtn"
                            onclick="document.getElementById('formAction').value='draft'"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-950/10 bg-stone-50 px-5 py-3.5 text-base font-extrabold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                        >
                            {{ __('Salvesta') }}
                        </button>

                        <button
                            type="submit"
                            id="publishListingBtn"
                            onclick="document.getElementById('formAction').value='publish'"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3.5 text-base font-extrabold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                        >
                            {{ __('Avalda') }}
                        </button>
                    @else
                        <button
                            type="submit"
                            id="saveListingChangesBtn"
                            onclick="document.getElementById('formAction').value='publish'"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3.5 text-base font-extrabold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20 sm:col-span-2"
                        >
                            {{ __('Salvesta muudatused') }}
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-layouts.app.public>