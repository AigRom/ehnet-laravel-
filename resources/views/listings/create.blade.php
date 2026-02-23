<x-layouts.app.public :title="__('Lisa kuulutus')">
    <flux:main>
        <div class="max-w-3xl space-y-6">

            {{-- Header --}}
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('Lisa kuulutus') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Lisa pildid ja põhiinfo. Hiljem saad kuulutust alati täiendada.') }}
                </flux:text>
            </div>

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('listings.store') }}"
                  class="space-y-6"
                  enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="action" id="formAction" value="publish">

                {{-- Card: Pildid --}}
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Pildid') }}
                            </div>
                            <div class="text-sm text-zinc-500">
                                {{ __('Lisa kuni 10 pilti. Esimene pilt on kaanepilt.') }}
                            </div>
                        </div>
                        <div class="text-xs text-zinc-500">
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
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div id="imagePreview" class="grid grid-cols-3 gap-3"></div>

                    {{-- Modal --}}
                    <div id="imageModal" class="fixed inset-0 hidden z-50 items-center justify-center bg-black/80 p-4">
                        <div class="relative w-full max-w-5xl">
                            <button type="button" id="imageModalClose"
                                    class="absolute -top-12 right-0 text-white text-sm px-3 py-2 rounded-lg bg-black/40 hover:bg-black/60">
                                Close
                            </button>

                            <button type="button" id="imageModalPrev"
                                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 md:-translate-x-10
                                           text-white w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 flex items-center justify-center">
                                ‹
                            </button>

                            <button type="button" id="imageModalNext"
                                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 md:translate-x-10
                                           text-white w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 flex items-center justify-center">
                                ›
                            </button>

                            <div class="rounded-2xl overflow-hidden bg-black/20 border border-white/10">
                                <img id="imageModalImg"
                                     class="w-full h-[75vh] object-contain"
                                     alt="">
                            </div>

                            <div id="imageModalCounter" class="mt-3 text-center text-sm text-white/80"></div>
                        </div>
                    </div>
                </div>

                {{-- Card: Põhiinfo --}}
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm p-5 space-y-5">
                    <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('Põhiinfo') }}
                    </div>

                    {{-- Title --}}
                    <div>
                        <flux:input
                            id="title"
                            name="title"
                            :label="__('Pealkiri')"
                            :value="old('title')"
                            required
                            maxlength="140"
                            placeholder="Nt. Kipsplaatide jäägid"
                        />
                        @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Condition (optional) --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                            {{ __('Seisukord') }} <span class="text-xs text-zinc-500">{{ __('(valikuline)') }}</span>
                        </label>

                        <div class="grid grid-cols-3 gap-2">
                            @php $cond = old('condition'); @endphp
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="radio" name="condition" value="new" @checked($cond === 'new')>
                                <span class="text-sm">{{ __('Uus') }}</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="radio" name="condition" value="used" @checked($cond === 'used')>
                                <span class="text-sm">{{ __('Kasutatud') }}</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="radio" name="condition" value="leftover" @checked($cond === 'leftover')>
                                <span class="text-sm">{{ __('Jääk') }}</span>
                            </label>
                        </div>

                        @error('condition')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                            {{ __('Kategooria') }}
                        </label>

                        <select
                            id="category_id"
                            name="category_id"
                            required
                            class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
                        >
                            <option value="">{{ __('Vali kategooria') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                    {{ $cat->name_et }}
                                </option>
                            @endforeach
                        </select>

                        @error('category_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Location (sinu Livewire autocomplete) --}}
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
                            <flux:button type="button" variant="primary" class="mt-2" @click="useMyLocation()">
                                {{ __('Kasuta minu asukohta') }}
                            </flux:button>
                        @endif

                        @error('location_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kättesaamine (checkboxid) --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                            {{ __('Kättesaamine') }} <span class="text-xs text-zinc-500">{{ __('(võid valida mitu)') }}</span>
                        </label>

                        @php
                            $delivery = old('delivery_options', []); // array
                            if (!is_array($delivery)) $delivery = [];
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="checkbox" name="delivery_options[]" value="pickup" @checked(in_array('pickup', $delivery, true))>
                                <span class="text-sm">{{ __('Järeletulemine') }}</span>
                            </label>

                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="checkbox" name="delivery_options[]" value="seller_delivery" @checked(in_array('seller_delivery', $delivery, true))>
                                <span class="text-sm">{{ __('Transpordi võimalus') }}</span>
                            </label>

                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="checkbox" name="delivery_options[]" value="courier" @checked(in_array('courier', $delivery, true))>
                                <span class="text-sm">{{ __('Saadan kulleriga või pakiautomaati') }}</span>
                            </label>

                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="checkbox" name="delivery_options[]" value="agreement" @checked(in_array('agreement', $delivery, true))>
                                <span class="text-sm">{{ __('Lepime kokku') }}</span>
                            </label>
                        </div>

                        @error('delivery_options')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        @error('delivery_options.*')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- Price mode --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                            {{ __('Hind') }}
                        </label>

                        @php
                            $priceMode = old('price_mode', old('price') === '0' ? 'free' : (old('price') === null || old('price') === '' ? 'deal' : 'price'));
                        @endphp

                        <div class="grid grid-cols-3 gap-2" x-data="{ mode: @js($priceMode) }">
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="radio" name="price_mode" value="deal" x-model="mode">
                                <span class="text-sm">{{ __('Kokkuleppel') }}</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="radio" name="price_mode" value="free" x-model="mode">
                                <span class="text-sm">{{ __('Tasuta') }}</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 cursor-pointer">
                                <input type="radio" name="price_mode" value="price" x-model="mode">
                                <span class="text-sm">{{ __('Hind') }}</span>
                            </label>

                            <div class="col-span-3" x-show="mode === 'price'">
                                <flux:input
                                    id="price"
                                    name="price"
                                    :label="__('Summa (EUR)')"
                                    :value="old('price')"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Nt. 25.00"
                                />
                            </div>

                            {{-- hidden normalize --}}
                            <input type="hidden" name="price_normalized" x-bind:value="mode">
                        </div>

                        @error('price')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <flux:textarea
                            id="description"
                            name="description"
                            :label="__('Kirjeldus')"
                        
                            maxlength="5000"
                            rows="6"
                            placeholder="Kirjelda kogust, mõõte, seisukorda ja kättesaamise tingimusi."
                        >{{ old('description') }}</flux:textarea>

                        @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-3">
                    <flux:button type="button" variant="primary" class="w-full" id="openListingPreview">
                        {{ __('Kuulutuse eelvaade') }}
                    </flux:button>

                    <x-listings.preview />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <flux:button type="submit" variant="primary" class="w-full">
                            {{ __('Avalda') }}
                        </flux:button>

                        <flux:button type="submit" variant="ghost" class="w-full" id="saveDraftBtn">
                            {{ __('Salvesta mustandina') }}
                        </flux:button>
                    </div>
                </div>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', () => {
              // draft action
              const draftBtn = document.getElementById('saveDraftBtn');
              const actionInput = document.getElementById('formAction');
              if (draftBtn && actionInput) {
                draftBtn.addEventListener('click', () => { actionInput.value = 'draft'; });
              }

              // price normalization (deal/free)
              const mode = document.querySelector('input[name="price_normalized"]');
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
    </flux:main>
</x-layouts.app.public>
