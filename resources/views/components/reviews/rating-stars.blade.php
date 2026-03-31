@props([
    'name' => 'rating',
    'initialRating' => 0,
    'label' => null,
])

<div x-data="{ rating: {{ (int) $initialRating }}, hoverRating: 0 }">
    @if($label)
        <label class="mb-3 block text-sm font-medium text-zinc-800">
            {{ $label }}
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="rating" />

    <div class="rounded-3xl border border-zinc-200 bg-zinc-50/70 p-4 sm:p-5">
        <div class="flex items-center gap-1 sm:gap-2">
            @for($i = 1; $i <= 5; $i++)
                <button
                    type="button"
                    @click="rating = {{ $i }}"
                    @mouseenter="hoverRating = {{ $i }}"
                    @mouseleave="hoverRating = 0"
                    class="group rounded-2xl p-1 transition focus:outline-none focus:ring-2 focus:ring-violet-300"
                    aria-label="{{ trans_choice(':count täht|:count tähte', $i, ['count' => $i]) }}"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="h-9 w-9 sm:h-10 sm:w-10 transition"
                        :class="((hoverRating || rating) >= {{ $i }}) ? 'text-amber-400 scale-105' : 'text-zinc-300'"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </button>
            @endfor
        </div>

        <div class="mt-3 text-sm font-medium text-zinc-700">
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
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>