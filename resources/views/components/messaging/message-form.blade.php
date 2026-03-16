@guest
<div class="text-sm text-zinc-600">
    {{ __('Sõnumi saatmiseks') }}
    <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
        {{ __('logi sisse') }}
    </a>.
</div>
@endguest
@if(auth()->check() && auth()->id() !== $listing->user_id)

<form method="POST" action="{{ route('listings.message.store', $listing) }}" class="mt-6">
    @csrf

    <div class="space-y-3">

        <textarea
            name="body"
            rows="4"
            required
            placeholder="Kirjuta müüjale sõnum..."
            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3 text-sm"
        ></textarea>

        <button
            type="submit"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700"
        >
            Saada sõnum
        </button>

    </div>

</form>

@endif