<x-layouts.app.public :title="$listing->title">
    <div class="mx-auto max-w-5xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.back-button
            :href="route('listings.mine', ['listing' => $listing->id])"
            :label="__('Tagasi minu kuulutuste juurde')"
            class="lg:hidden"
        />

        @include('user.listings.partials.detail-panel', [
            'listing' => $listing,
        ])
    </div>
</x-layouts.app.public>