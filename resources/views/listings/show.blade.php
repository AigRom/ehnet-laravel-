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
            Sõnum müüjale
        ========================================================== --}}
        <div class="max-w-xl">

            <h2 class="text-lg font-semibold mb-3">
                {{ __('Küsi müüjalt') }}
            </h2>

            @guest
                <div class="text-sm text-zinc-600">
                    {{ __('Sõnumi saatmiseks') }}
                    <a href="{{ route('login') }}" class="text-emerald-600 hover:underline">
                        {{ __('logi sisse') }}
                    </a>.
                </div>
            @endguest

            @auth
                @if(auth()->id() !== $listing->user_id)

                    <form method="POST" action="{{ route('listings.conversation.open', $listing) }}">
                        @csrf

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700"
                        >
                            {{ __('Saada sõnum') }}
                        </button>
                    </form>

                @else
                    <div class="text-sm text-zinc-500">
                        {{ __('See on sinu kuulutus.') }}
                    </div>
                @endif
            @endauth

        </div>

    </div>

</x-layouts.app.public>