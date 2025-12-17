<x-layouts.app.sidebar :title="__('Add listing')">
    <flux:main>
        <div class="max-w-2xl space-y-6">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('Add listing') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Fill in the details. Location autocomplete and image upload will be added next.') }}
                </flux:text>
            </div>

            <form method="POST" action="{{ route('listings.store') }}" class="space-y-6">
                @csrf

                {{-- Title --}}
                <div>
                    <flux:input
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
                        name="description"
                        :label="__('Description')"
                        required
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
                            // Kui old('location_id') puudub ja useril on location, siis setime vaikimisi.
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

                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Publish listing') }}
                </flux:button>
            </form>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
