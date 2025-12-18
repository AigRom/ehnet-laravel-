<x-layouts.app.sidebar :title="__('Add listing')">
    <flux:main>
        <div class="max-w-2xl space-y-6">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('Add listing') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Fill in the details, add images and preview the listing before publishing.') }}
                </flux:text>
            </div>

            <form method="POST"
                  action="{{ route('listings.store') }}"
                  class="space-y-6"
                  enctype="multipart/form-data">
                @csrf

                {{-- Title --}}
                <div>
                    <flux:input
                        id="title"
                        name="title"
                        :label="__('Title')"
                        :value="old('title')"
                        required
                        maxlength="140"
                        placeholder="Nt. Kipsplaatide jäägid"
                    />
                    @error('title')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <flux:textarea
                        id="description"
                        name="description"
                        :label="__('Description')"
                        required
                        minlength="20"
                        rows="6"
                        placeholder="Kirjelda kogust, mõõte, seisukorda ja kättesaamise tingimusi."
                    >{{ old('description') }}</flux:textarea>

                    @error('description')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                        {{ __('Category') }}
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
                    >
                        <option value="">{{ __('Select category') }}</option>
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

                {{-- Location (Livewire autocomplete + "use my location") --}}
                @php
                    $userLocationId = auth()->user()->location_id ?? null;
                    $initialLocationId = old('location_id') ?? $userLocationId;
                @endphp

                <div
                    class="relative overflow-visible"
                    x-data="{
                        useMyLocation: {{ $userLocationId ? 'true' : 'false' }},
                        myLocationId: {{ $userLocationId ? (int) $userLocationId : 'null' }},
                        initId: {{ $initialLocationId ? (int) $initialLocationId : 'null' }},
                        init() {
                            if (!{{ old('location_id') ? 'true' : 'false' }} && this.myLocationId) {
                                this.$nextTick(() => Livewire.dispatch('loc:set', { id: this.myLocationId }));
                            }
                        }
                    }"
                    x-init="init()"
                    @loc:selected.window="useMyLocation = false"
                >
                    @if($userLocationId)
                        <label class="mt-1 mb-2 inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                            <input
                                type="checkbox"
                                class="h-4 w-4 border-zinc-300 text-zinc-900"
                                x-model="useMyLocation"
                                @change="
                                    if (useMyLocation && myLocationId) {
                                        Livewire.dispatch('loc:set', { id: myLocationId });
                                    }
                                "
                            >
                            <span>{{ __('Use my location') }}</span>
                        </label>
                    @endif

                    <livewire:location-autocomplete
                        :initial-id="$initialLocationId"
                        :wire:key="'loc-'.($initialLocationId ?? 'new')"
                    />
                </div>

                {{-- Price --}}
                <div>
                    <flux:input
                        id="price"
                        name="price"
                        :label="__('Price (EUR)')"
                        :value="old('price')"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0 = tasuta, tühjaks = kokkuleppel"
                    />
                    @error('price')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Images --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                        {{ __('Images') }}
                        <span class="text-xs text-zinc-500">{{ __('(drag to reorder, click to view)') }}</span>
                    </label>

                    <input
                        id="images"
                        type="file"
                        name="images[]"
                        multiple
                        accept="image/*"
                        class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
                    />

                    <input type="hidden" name="images_order" id="images_order" value="[]">

                    @error('images')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    <div id="imagePreview" class="mt-3 grid grid-cols-3 gap-3"></div>

                    <p class="mt-2 text-xs text-zinc-500">
                        {{ __('Up to 10 images. The first image will be used as the cover image.') }}
                    </p>
                </div>

                {{-- Eelvaate nupp (MITTE submit) --}}
                <flux:button type="button" variant="primary" class="w-full" id="openListingPreview">
                    {{ __('Kuulutuse eelvaade') }}
                </flux:button>

                {{-- Preview modal (komponent) peab olema vormi sees, et "Lisa kuulutus" saaks submitida --}}
                <x-listings.preview />
            </form>

            {{-- Image modal (suur pilt + rotate) - kui sul see juba mujal on, ära dubleeri --}}
            <div id="imageModal" class="fixed inset-0 hidden z-50 items-center justify-center bg-black/70 p-4">
                <div class="relative max-w-3xl w-full">
                    <div class="absolute -top-10 right-0 flex gap-2">
                        <button type="button" id="imageModalRotate"
                                class="text-white text-sm px-3 py-2 rounded-lg bg-black/40">
                            Rotate
                        </button>
                        <button type="button" id="imageModalClose"
                                class="text-white text-sm px-3 py-2 rounded-lg bg-black/40">
                            Close
                        </button>
                    </div>

                    <img id="imageModalImg"
                         class="w-full max-h-[80vh] object-contain rounded-xl"
                         alt="">
                </div>
            </div>

        </div>
    </flux:main>
</x-layouts.app.sidebar>
