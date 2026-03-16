<x-layouts.app.public :title="$listing->title ?? __('Kuulutus')">

    {{-- Lehe sisu konteiner --}}
    <div class="mx-auto max-w-7xl px-4 py-6 md:py-8 space-y-6">

        {{-- ==========================================================
            Ülemine navirida
        ========================================================== --}}
        <div class="flex items-center justify-between">

            <a 
                href="{{ url()->previous() }}" 
                class="text-sm text-blue-600 hover:underline"
            >
                ← {{ __('Tagasi') }}
            </a>

            <a 
                href="{{ route('listings.index') }}" 
                class="text-sm text-zinc-600 hover:underline dark:text-zinc-300"
            >
                {{ __('Kõik kuulutused') }}
            </a>
        </div>

        {{-- ==========================================================
            Kuulutuse detail
        ========================================================== --}}
        <x-listings.detail
            mode="db"
            :listing="$listing"
        />

        {{-- ==========================================================
            TEST – sõnum müüjale
        ========================================================== --}}
        <div class="max-w-xl">

            <h2 class="text-lg font-semibold mb-3">
                {{ __('Küsi müüjalt') }}
            </h2>

            <x-messaging.message-form :listing="$listing" />

        </div>

    </div>

</x-layouts.app.public>