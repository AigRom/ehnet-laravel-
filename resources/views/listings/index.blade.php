<x-layouts.app.public :title="__('Kuulutused')">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                {{ __('Kuulutused') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Sirvi ja otsi EHNETi kuulutusi.') }}
            </p>
        </div>

        
    </div>

    <x-listings.search-bar :categories="$categories" />

    {{-- Grid --}}
    @if($listings->count() > 0)
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($listings as $listing)
                {{-- Kui sul on olemas card komponent, kasuta seda --}}
                <x-listings.card :listing="$listing" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $listings->links() }}
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-8 text-center
                    dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                <x-icons.squares-2x2 class="h-6 w-6 text-zinc-600 dark:text-zinc-200" />
            </div>
            <h2 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Tulemusi ei leitud') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Proovi teist otsingusõna või tühjenda filtrid.') }}
            </p>
            <div class="mt-4">
                <a href="{{ url('/listings') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-900
                          hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800/60">
                    {{ __('Tühjenda filtrid') }}
                </a>
            </div>
        </div>
    @endif
</x-layouts.app.public>