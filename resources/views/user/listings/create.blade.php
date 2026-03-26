<x-layouts.app.public :title="__('Lisa kuulutus')">
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- Header --}}
        <div class="space-y-2">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                {{ __('Lisa kuulutus') }}
            </h1>

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Lisa pildid ja põhiinfo. Hiljem saad kuulutust alati täiendada.') }}
            </p>
        </div>

        <form method="POST"
              action="{{ route('listings.store') }}"
              class="space-y-6"
              enctype="multipart/form-data"
              novalidate>
            @csrf

            <input type="hidden" name="action" id="formAction" value="publish">

            {{-- Card: Pildid --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Pildid') }}
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Lisa kuni 10 pilti. Esimene pilt on kaanepilt.') }}
                        </div>
                    </div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Lohista järjekorra muutmiseks') }}
                    </div>
                </div>

                <input
                    id="images"
                    type="file"
                    name="images[]"
                    multiple
                    accept="image/*"
                    class="hidden"
                />
                <input type="hidden" name="images_order" id="images_order" value="[]">

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

                <div id="imagePreview" class="grid grid-cols-3 gap-3"></div>

                {{-- Modal --}}
                <div id="imageModal" class="fixed inset-0 hidden z-50 items-center justify-center bg-black/80 p-4">
                    <div class="relative w-full max-w-5xl">
                        <button
                            type="button"
                            id="imageModalClose"
                            class="absolute -top-12 right-0 rounded-lg bg-black/40 px-3 py-2 text-sm text-white hover:bg-black/60"
                        >
                            {{ __('Sulge') }}
                        </button>

                        <button
                            type="button"
                            id="imageModalPrev"
                            class="absolute left-0 top-1/2 flex h-10 w-10 -translate-x-2 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60 md:-translate-x-10"
                        >
                            ‹
                        </button>

                        <button
                            type="button"
                            id="imageModalNext"
                            class="absolute right-0 top-1/2 flex h-10 w-10 translate-x-2 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60 md:translate-x-10"
                        >
                            ›
                        </button>

                        <div class="overflow-hidden rounded-2xl border border-white/10 bg-black/20">
                            <img id="imageModalImg" class="h-[75vh] w-full object-contain" alt="">
                        </div>

                        <div id="imageModalCounter" class="mt-3 text-center text-sm text-white/80"></div>
                    </div>
                </div>
            </div>

            {{-- Card: Põhiinfo --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Põhiinfo') }}
                </div>

                {{-- Title --}}
                <div>
                    <label for="title" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
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
                            'block w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition dark:bg-zinc-900 dark:text-white',
                            'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30' => !$errors->has('title'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:border-red-800 dark:focus:border-red-500 dark:focus:ring-red-900/30' => $errors->has('title'),
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
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Seisukord') }} <span class="text-xs text-zinc-500">{{ __('(valikuline)') }}</span>
                    </label>

                    @php $cond = old('condition'); @endphp

                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="radio" name="condition" value="new" @checked($cond === 'new')>
                            <span class="text-sm">{{ __('Uus') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="radio" name="condition" value="used" @checked($cond === 'used')>
                            <span class="text-sm">{{ __('Kasutatud') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="radio" name="condition" value="leftover" @checked($cond === 'leftover')>
                            <span class="text-sm">{{ __('Jääk') }}</span>
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
                    <label for="category_id" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Kategooria') }}
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                        @class([
                            'w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition dark:bg-zinc-900 dark:text-white',
                            'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30' => !$errors->has('category_id'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:border-red-800 dark:focus:border-red-500 dark:focus:ring-red-900/30' => $errors->has('category_id'),
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
                        <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('või') }}</div>
                        <button
                            type="button"
                            @click="useMyLocation()"
                            class="mt-2 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
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
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Kättesaamine') }} <span class="text-xs text-zinc-500">{{ __('(võid valida mitu)') }}</span>
                    </label>

                    @php
                        $delivery = old('delivery_options', []);
                        if (!is_array($delivery)) $delivery = [];
                    @endphp

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="checkbox" name="delivery_options[]" value="pickup" @checked(in_array('pickup', $delivery, true))>
                            <span class="text-sm">{{ __('Järeletulemine') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="checkbox" name="delivery_options[]" value="seller_delivery" @checked(in_array('seller_delivery', $delivery, true))>
                            <span class="text-sm">{{ __('Transpordi võimalus') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="checkbox" name="delivery_options[]" value="courier" @checked(in_array('courier', $delivery, true))>
                            <span class="text-sm">{{ __('Saadan kulleriga või pakiautomaati') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
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
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Hind') }}
                    </label>

                    @php
                        $priceMode = old('price_mode', old('price') === '0' ? 'free' : (old('price') === null || old('price') === '' ? 'deal' : 'price'));
                    @endphp

                    <div class="grid grid-cols-3 gap-2" x-data="{ mode: @js($priceMode) }">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="radio" name="price_mode" value="deal" x-model="mode">
                            <span class="text-sm">{{ __('Kokkuleppel') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="radio" name="price_mode" value="free" x-model="mode">
                            <span class="text-sm">{{ __('Tasuta') }}</span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                            <input type="radio" name="price_mode" value="price" x-model="mode">
                            <span class="text-sm">{{ __('Hind') }}</span>
                        </label>

                        <div class="col-span-3" x-show="mode === 'price'">
                            <label for="price" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
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
                                    'block w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition dark:bg-zinc-900 dark:text-white',
                                    'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30' => !$errors->has('price'),
                                    'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:border-red-800 dark:focus:border-red-500 dark:focus:ring-red-900/30' => $errors->has('price'),
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
                    <label for="description" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                        {{ __('Kirjeldus') }}
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        maxlength="5000"
                        rows="6"
                        placeholder="Kirjelda kogust, mõõte, seisukorda ja kättesaamise tingimusi."
                        @class([
                            'block w-full rounded-xl bg-white p-3 text-sm text-zinc-900 outline-none transition dark:bg-zinc-900 dark:text-white',
                            'border border-zinc-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-zinc-700 dark:focus:border-emerald-500 dark:focus:ring-emerald-900/30' => !$errors->has('description'),
                            'border border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100 dark:border-red-800 dark:focus:border-red-500 dark:focus:ring-red-900/30' => $errors->has('description'),
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
                    class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                >
                    {{ __('Kuulutuse eelvaade') }}
                </button>

                <x-listings.preview />

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                    >
                        {{ __('Avalda') }}
                    </button>

                    <button
                        type="submit"
                        id="saveDraftBtn"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
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