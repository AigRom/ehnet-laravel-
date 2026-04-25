<x-layouts.app.public :title="'EHNET'" :container="false">
    <div class="space-y-12">
        <x-home.hero :categories="$categories" />

        <div class="mx-auto max-w-[1500px] px-4 sm:px-6 lg:px-8">
            <x-home.featured-grid :listings="$featured" />
        </div>

        <div class="mx-auto max-w-[1500px] px-4 sm:px-6 lg:px-8">
            <x-home.latest-grid :listings="$latest" />
        </div>

        
        <x-home.value-panel />
        
    </div>
</x-layouts.app.public>