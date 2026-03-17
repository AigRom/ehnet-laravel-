@props([
    'conversation',
])

<div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 md:p-5">
    <form method="POST" action="{{ route('messages.store', $conversation) }}" class="space-y-3">
        @csrf

        <div>
            <label for="body" class="sr-only">{{ __('Sõnum') }}</label>

            <textarea
                id="body"
                name="body"
                rows="4"
                required
                placeholder="{{ __('Kirjuta vastus...') }}"
                class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
            >{{ old('body') }}</textarea>

            @error('body')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-zinc-500">
                {{ __('Sõnum saadetakse EHNETi vestluse kaudu.') }}
            </p>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
            >
                {{ __('Saada') }}
            </button>
        </div>
    </form>
</div>