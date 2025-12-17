<div class="relative overflow-visible" wire:key="loc-autocomplete-root">
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
        {{ __('Location') }}
    </label>

    <input
        type="text"
        wire:model.live="search"
        autocomplete="off"
        placeholder="Alusta trükkimist (nt Haabersti, Valtu...)"
        class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3"
    />

    {{-- hidden field, mis läheb formiga kaasa (POST) --}}
    <input type="hidden" name="location_id" value="{{ $selectedId ?? $location_id ?? '' }}">

    {{-- Dropdown (näita kui on midagi otsida ja kasutaja pole veel valikut teinud) --}}
    @if(mb_strlen(trim($search)) >= 2 && $selectedId === null)
        <div
            class="absolute left-0 right-0 z-[99999] mt-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-lg max-h-64 overflow-auto"
            wire:ignore.self
        >
            @if(!empty($results))
                @foreach($results as $item)
                    <button
                        type="button"
                        class="w-full text-left px-4 py-3 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                        wire:click="selectLocation({{ $item['id'] }})"
                    >
                        {{ $item['label'] }}
                    </button>
                @endforeach
            @else
                <div class="p-3 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('No results') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Clear nupp --}}
    @if($selectedId || $location_id)
        <div class="mt-2">
            <button
                type="button"
                wire:click="clearSelection"
                class="px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-700 text-sm"
            >
                {{ __('Clear location') }}
            </button>
        </div>
    @endif

    {{-- Valideerimisviga --}}
    @error('location_id')
        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>
