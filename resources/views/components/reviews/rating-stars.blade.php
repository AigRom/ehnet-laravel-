@props([
    'name' => 'rating',
    'initialRating' => 0,
    'label' => null,
])

<div x-data="{ rating: {{ (int) $initialRating }}, hoverRating: 0 }">
    @if($label)
        <label class="mb-3 block text-sm font-bold text-emerald-950">
            {{ $label }}
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="rating" />

    <div class="rounded-3xl border border-emerald-950/10 bg-stone-50 p-4 sm:p-5">
        <div class="flex items-center gap-1 sm:gap-2">
            @for($i = 1; $i <= 5; $i++)
                <button
                    type="button"
                    @click="rating = {{ $i }}"
                    @mouseenter="hoverRating = {{ $i }}"
                    @mouseleave="hoverRating = 0"
                    class="group rounded-2xl p-1.5 transition hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-200/60"
                    :aria-pressed="rating === {{ $i }} ? 'true' : 'false'"
                    aria-label="{{ trans_choice(':count täht|:count tähte', $i, ['count' => $i]) }}"
                >
                    <x-icons.star
                        class="h-9 w-9 transition sm:h-10 sm:w-10"
                        x-bind:class="((hoverRating || rating) >= {{ $i }}) ? 'text-amber-500 scale-105' : 'text-zinc-300'"
                    />
                </button>
            @endfor
        </div>

        <div class="mt-3 text-sm font-bold text-emerald-950">
            <template x-if="rating === 0">
                <span>{{ __('Vali hinnang 1–5') }}</span>
            </template>

            <template x-if="rating === 1">
                <span>{{ __('Väga halb') }}</span>
            </template>

            <template x-if="rating === 2">
                <span>{{ __('Kehv') }}</span>
            </template>

            <template x-if="rating === 3">
                <span>{{ __('Rahuldav') }}</span>
            </template>

            <template x-if="rating === 4">
                <span>{{ __('Hea') }}</span>
            </template>

            <template x-if="rating === 5">
                <span>{{ __('Väga hea') }}</span>
            </template>
        </div>
    </div>

    @error($name)
        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>