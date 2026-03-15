<x-layouts.app.public :title="'EHNET'">
    <div class="mx-auto max-w-7xl px-4 py-8 space-y-12">
        <x-listings.search-bar :categories="$categories" />
        <x-home.featured-grid :listings="$featured" />
        <x-home.latest-grid :listings="$latest" />
    </div>
</x-layouts.app.public>
