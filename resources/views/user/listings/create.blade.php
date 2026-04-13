<x-layouts.app.public :title="__('Lisa kuulutus')">
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- Header --}}
        <div class="space-y-2">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
                {{ __('Lisa kuulutus') }}
            </h1>

            <p class="text-sm text-zinc-500">
                {{ __('Lisa pildid ja põhiinfo. Hiljem saad kuulutust alati täiendada.') }}
            </p>
        </div>

        <form method="POST"
              action="{{ route('listings.store') }}"
              class="space-y-6"
              enctype="multipart/form-data"
              novalidate>
            @csrf

            <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
            <input type="hidden" name="action" id="formAction" value="publish">

            {{-- Card: Pildid --}}
            <div
                x-data="listingImagesCreate()"
                class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm space-y-3"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-base font-semibold text-zinc-900">
                            {{ __('Pildid') }}
                        </div>
                        <div class="text-sm text-zinc-500">
                            {{ __('Lisa kuni 10 pilti. Esimene pilt on kaanepilt.') }}
                        </div>
                    </div>

                    <div class="text-xs text-zinc-500 text-right">
                        {{ __('Järjekorda muuda nuppudega ↑ ↓') }}
                    </div>
                </div>

                <input
                    x-ref="input"
                    id="images"
                    type="file"
                    name="images[]"
                    multiple
                    accept="image/*"
                    class="hidden"
                    @change="handleFiles($event)"
                >

                <input type="hidden" name="images_order" id="images_order" x-model="imagesOrderJson">

                @error('images')
                    <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror

                @error('images.*')
                    <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror

                <div data-listing-images-grid class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    <template x-for="(item, index) in items" :key="item.uid">
                        <div class="relative aspect-square overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                            <img
                                :src="item.preview"
                                :alt="item.file.name"
                                class="h-full w-full cursor-zoom-in object-cover"
                                @click="openModal(index)"
                            >

                            <div class="absolute left-1 top-1 rounded-lg bg-black/60 px-2 py-1 text-[10px] text-white">
                                <span x-text="index === 0 ? 'Kaanepilt' : `#${index + 1}`"></span>
                            </div>

                            <div class="absolute right-1 top-1 flex gap-1">
                                <button
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-black/60 text-xs text-white disabled:opacity-40"
                                    @click="moveUp(index)"
                                    :disabled="index === 0"
                                    title="Liiguta üles"
                                >
                                    ↑
                                </button>

                                <button
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-black/60 text-xs text-white disabled:opacity-40"
                                    @click="moveDown(index)"
                                    :disabled="index === items.length - 1"
                                    title="Liiguta alla"
                                >
                                    ↓
                                </button>

                                <button
                                    type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-black/60 text-sm text-white"
                                    @click="remove(index)"
                                    title="Eemalda"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </template>

                    <button
                        x-show="items.length < maxImages"
                        type="button"
                        @click="$refs.input.value = null; $refs.input.click()"
                        class="flex aspect-square items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-white text-zinc-500 hover:text-zinc-700"
                    >
                        <div class="flex flex-col items-center gap-1">
                            <div class="text-3xl leading-none">+</div>
                            <div class="text-xs">{{ __('Lisa') }}</div>
                        </div>
                    </button>
                </div>

                {{-- Modal --}}
                <div
                    x-show="modalOpen"
                    x-transition.opacity
                    @keydown.window.escape="closeModal()"
                    @keydown.window.arrow-left="if (modalOpen) prevModal()"
                    @keydown.window.arrow-right="if (modalOpen) nextModal()"
                    @click.self="closeModal()"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                    style="display: none;"
                >
                    <div class="relative w-full max-w-5xl">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="absolute -top-12 right-0 rounded-lg bg-black/40 px-3 py-2 text-sm text-white hover:bg-black/60"
                        >
                            {{ __('Sulge') }}
                        </button>

                        <button
                            type="button"
                            @click="prevModal()"
                            class="absolute left-0 top-1/2 flex h-10 w-10 -translate-x-2 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60 md:-translate-x-10"
                        >
                            ‹
                        </button>

                        <button
                            type="button"
                            @click="nextModal()"
                            class="absolute right-0 top-1/2 flex h-10 w-10 translate-x-2 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60 md:translate-x-10"
                        >
                            ›
                        </button>

                        <div class="overflow-hidden rounded-2xl border border-white/10 bg-black/20">
                            <img :src="modalImageSrc()" class="h-[75vh] w-full object-contain" alt="">
                        </div>

                        <div class="mt-3 text-center text-sm text-white/80">
                            <span x-text="modalCounterText()"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Põhiinfo --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm space-y-5">
                <div class="text-base font-semibold text-zinc-900">
                    {{ __('Põhiinfo') }}
                </div>

                {{-- Title --}}
                <div>
                    <label for="title" class="mb-2 block text-sm font-medium text-zinc-700">
                        {{ __('Pealkiri') }}
                    </label>

                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title') }}"
                        required
                        maxlength="140"
                        placeholder="Nt. Kipsplaatide jäägid"
                        @class([
                            'block w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition',
                            'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100' => !$errors->has('title'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('title'),
                        ])
                    >

                    @error('title')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Condition (optional) --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700">
                        {{ __('Seisukord') }} <span class="text-xs text-zinc-500">{{ __('(valikuline)') }}</span>
                    </label>

                    @php $cond = old('condition'); @endphp

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 py-3">
                            <input type="radio" name="condition" value="new" @checked($cond === 'new')>
                            <span class="text-sm leading-snug">{{ __('Uus') }}</span>
                        </label>

                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 py-3">
                            <input type="radio" name="condition" value="used" @checked($cond === 'used')>
                            <span class="text-sm leading-snug">{{ __('Kasutatud') }}</span>
                        </label>

                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 py-3">
                            <input type="radio" name="condition" value="leftover" @checked($cond === 'leftover')>
                            <span class="text-sm leading-snug">{{ __('Jääk') }}</span>
                        </label>
                    </div>

                    @error('condition')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-medium text-zinc-700">
                        {{ __('Kategooria') }}
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                        @class([
                            'w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition',
                            'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100' => !$errors->has('category_id'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('category_id'),
                        ])
                    >
                        <option value="">{{ __('Vali kategooria') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                {{ $cat->name_et }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Location --}}
                @php
                    $userLocationId = auth()->user()->location_id ?? null;
                    $initialLocationId = old('location_id') ?? null;
                    $userLocationLabel = auth()->user()->location?->full_label_et ?? null;
                @endphp

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
                    @loc:selected.window="
                        const el = document.getElementById('location_label');
                        if (el && $event.detail && $event.detail.label !== undefined) {
                            el.value = $event.detail.label || '';
                        }
                    "
                    @loc:clear.window="
                        const el = document.getElementById('location_label');
                        if (el) el.value = '';
                    "
                >
                    <livewire:location-autocomplete
                        :initial-id="$initialLocationId"
                        :wire:key="'loc-'.($initialLocationId ?? 'new')"
                    />

                    <input
                        type="hidden"
                        name="location_label"
                        id="location_label"
                        value="{{ old('location_label', '') }}"
                    >

                    @if($userLocationId)
                        <div class="mt-2 text-sm text-zinc-500">{{ __('või') }}</div>
                        <button
                            type="button"
                            @click="useMyLocation()"
                            class="mt-2 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
                        >
                            {{ __('Kasuta minu asukohta') }}
                        </button>
                    @endif

                    @error('location_id')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Kättesaamine --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700">
                        {{ __('Kättesaamine') }} <span class="text-xs text-zinc-500">{{ __('(võid valida mitu)') }}</span>
                    </label>

                    @php
                        $delivery = old('delivery_options', []);
                        if (!is_array($delivery)) $delivery = [];
                    @endphp

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3">
                            <input type="checkbox" name="delivery_options[]" value="pickup" @checked(in_array('pickup', $delivery, true))>
                            <span class="text-sm">{{ __('Järeletulemine') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3">
                            <input type="checkbox" name="delivery_options[]" value="seller_delivery" @checked(in_array('seller_delivery', $delivery, true))>
                            <span class="text-sm">{{ __('Transpordi võimalus') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3">
                            <input type="checkbox" name="delivery_options[]" value="courier" @checked(in_array('courier', $delivery, true))>
                            <span class="text-sm">{{ __('Saadan kulleriga või pakiautomaati') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3">
                            <input type="checkbox" name="delivery_options[]" value="agreement" @checked(in_array('agreement', $delivery, true))>
                            <span class="text-sm">{{ __('Lepime kokku') }}</span>
                        </label>
                    </div>

                    @error('delivery_options')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror

                    @error('delivery_options.*')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Price mode --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700">
                        {{ __('Hind') }}
                    </label>

                    @php
                        $priceMode = old('price_mode', old('price') === '0' ? 'free' : (old('price') === null || old('price') === '' ? 'deal' : 'price'));
                    @endphp

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3" x-data="{ mode: @js($priceMode) }">
                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 py-3">
                            <input type="radio" name="price_mode" value="deal" x-model="mode">
                            <span class="text-sm leading-snug">{{ __('Kokkuleppel') }}</span>
                        </label>

                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 py-3">
                            <input type="radio" name="price_mode" value="free" x-model="mode">
                            <span class="text-sm leading-snug">{{ __('Tasuta') }}</span>
                        </label>

                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 py-3">
                            <input type="radio" name="price_mode" value="price" x-model="mode">
                            <span class="text-sm leading-snug">{{ __('Hind') }}</span>
                        </label>

                        <div class="sm:col-span-3" x-show="mode === 'price'">
                            <label for="price" class="mb-2 block text-sm font-medium text-zinc-700">
                                {{ __('Summa (EUR)') }}
                            </label>

                            <input
                                id="price"
                                name="price"
                                type="number"
                                value="{{ old('price') }}"
                                step="0.01"
                                min="0"
                                placeholder="Nt. 25.00"
                                @class([
                                    'block w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition',
                                    'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100' => !$errors->has('price'),
                                    'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('price'),
                                ])
                            >
                        </div>

                        <input type="hidden" name="price_normalized" x-bind:value="mode">
                    </div>

                    @error('price')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-zinc-700">
                        {{ __('Kirjeldus') }}
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        maxlength="5000"
                        rows="6"
                        placeholder="Kirjelda kogust, mõõte, seisukorda ja kättesaamise tingimusi."
                        @class([
                            'block w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition',
                            'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100' => !$errors->has('description'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('description'),
                        ])
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <p class="mt-1 flex items-center gap-1 text-sm font-medium text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3">
                <button
                    type="button"
                    id="openListingPreview"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
                >
                    {{ __('Kuulutuse eelvaade') }}
                </button>

                <x-listings.preview />

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200"
                    >
                        {{ __('Avalda') }}
                    </button>

                    <button
                        type="submit"
                        id="saveDraftBtn"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                    >
                        {{ __('Salvesta mustandina') }}
                    </button>
                </div>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
          const draftBtn = document.getElementById('saveDraftBtn');
          const actionInput = document.getElementById('formAction');

          if (draftBtn && actionInput) {
            draftBtn.addEventListener('click', () => { actionInput.value = 'draft'; });
          }

          const priceInput = document.getElementById('price');
          const radios = document.querySelectorAll('input[name="price_mode"]');

          function applyPriceMode() {
            const checked = document.querySelector('input[name="price_mode"]:checked')?.value || 'deal';
            if (checked === 'deal') {
              if (priceInput) priceInput.value = '';
            }
            if (checked === 'free') {
              if (priceInput) priceInput.value = '0';
            }
          }

          radios.forEach(r => r.addEventListener('change', applyPriceMode));
          applyPriceMode();
        });
        </script>

    </div>
</x-layouts.app.public>