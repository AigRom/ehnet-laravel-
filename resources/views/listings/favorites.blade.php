<x-layouts.app.public :title="__('Lemmikud')">

    <div class="mx-auto max-w-6xl py-6">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Minu lemmikud') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Siia on salvestatud sinu lemmik kuulutused.') }}
            </p>
        </div>

        @if($listings->count())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($listings as $listing)
                    <x-listings.card :listing="$listing" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $listings->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 text-center
                        dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Sul ei ole veel lemmikuid') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Lisa kuulutusi lemmikutesse ja need ilmuvad siia.') }}
                </p>
            </div>
        @endif

    </div>

</x-layouts.app.public>