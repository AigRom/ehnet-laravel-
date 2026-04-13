@props(['listings'])

<section class="space-y-4">
    <h2 class="text-2xl font-semibold">Viimati lisatud</h2>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($listings as $listing)
            <x-listings.card :listing="$listing" />
        @empty
            <div class="text-neutral-500">Kuulutusi ei leitud.</div>
        @endforelse
    </div>
</section>