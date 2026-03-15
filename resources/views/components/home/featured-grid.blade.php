@props(['listings'])

<section class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">Esiletõstetud</h2>


    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($listings as $listing)
            <x-listings.card :listing="$listing" />
        @empty
            <div class="text-neutral-500">Esiletõstetud kuulutusi ei leitud.</div>
        @endforelse
    </div>
</section>