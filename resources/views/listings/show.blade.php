{{-- resources/views/listings/show.blade.php --}}
{{-- 
    EHNET – Public kuulutuse detailvaade

--}}

<x-layouts.app.public :title="$listing->title ?? __('Kuulutus')">

    {{-- Lehe sisu konteiner --}}
    <div class="mx-auto max-w-7xl px-4 py-6 md:py-8 space-y-6">

        {{-- ==========================================================
            Ülemine navirida
           ========================================================== --}}
        <div class="flex items-center justify-between">

            {{-- Tagasi eelmisele lehele (nt avaleht või otsingutulemused) --}}
            <a 
                href="{{ url()->previous() }}" 
                class="text-sm text-blue-600 hover:underline"
            >
                ← {{ __('Tagasi') }}
            </a>

            {{-- Link kõikide kuulutuste lehele --}}
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
        {{-- 
            mode="db" → kasutab päris andmeid andmebaasist
            :listing  → Eloquent mudel
            Pildid, hind, meta jne hallatakse detail-komponendis
        --}}
        <x-listings.detail
            mode="db"
            :listing="$listing"
        />

    </div>

</x-layouts.app.public>
