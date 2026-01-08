<x-layouts.app.sidebar :title="__('Muuda kuulutust')">
    <flux:main>
        <div class="max-w-2xl space-y-6">
            <div class="space-y-2">
                <flux:heading size="xl">{{ __('Muuda kuulutust') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Uuenda kuulutuse andmeid ja salvesta muudatused.') }}
                </flux:text>
            </div>

            <form method="POST"
                  action="{{ route('listings.mine.update', $listing) }}"
                  class="space-y-6"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                {{-- Title --}}
                <div>
                    <flux:input
                        id="title"
                        name="title"
                        :label="__('Pealkiri')"
                        :value="old('title', $listing->title)"
                        required
                        maxlength="140"
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
                        :label="__('Kirjeldus')"
                        required
                        minlength="20"
                        rows="6"
                    >{{ old('description', $listing->description) }}</flux:textarea>

                    @error('description')
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
                            <option value="{{ $cat->id }}" @selected(old('category_id', $listing->category_id) == $cat->id)>
                                {{ $cat->name_et }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Location (Livewire autocomplete + "Kasuta minu asukohta") --}}
                @php
                    $userLocationId = auth()->user()->location_id ?? null;
                    $userLocationLabel = auth()->user()->location?->full_label_et ?? null;

                    $currentLocationId = old('location_id', $listing->location_id);
                    $currentLocationLabel = old('location_label', $listing->location?->full_label_et ?? '');
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
                        :initial-id="$currentLocationId"
                        :wire:key="'loc-edit-'.$listing->id.'-'.($currentLocationId ?? 'new')"
                    />

                    {{-- label preview / (hiljem saad kasutada ka avalikus detailis vms) --}}
                    <input
                        type="hidden"
                        name="location_label"
                        id="location_label"
                        value="{{ $currentLocationLabel }}"
                    >

                    @if($userLocationId)
                        <div class="mt-2 text-sm text-zinc-500">
                            {{ __('või') }}
                        </div>

                        <flux:button
                            type="button"
                            variant="primary"
                            class="mt-2"
                            @click="useMyLocation()"
                        >
                            {{ __('Kasuta minu asukohta') }}
                        </flux:button>
                    @endif

                    @error('location_id')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price --}}
                <div>
                    <flux:input
                        id="price"
                        name="price"
                        :label="__('Hind (EUR)')"
                        :value="old('price', $listing->price)"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                    @error('price')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Images: jätame 1. sammus välja, lisame järgmises --}}
                <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 p-4 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Piltide muutmine lisame järgmises sammus.') }}
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:button variant="outline" :href="route('listings.mine.show', $listing)" wire:navigate>
                        {{ __('Tühista') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary">
                        {{ __('Salvesta muudatused') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
