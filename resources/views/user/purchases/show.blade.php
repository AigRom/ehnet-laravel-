<x-layouts.app.public :title="$listing?->title ?? __('Minu ost')">
    <div class="mx-auto max-w-5xl space-y-4 px-4 py-6 md:px-0">
        <x-ui.back-button
            :href="route('purchases.index', ['purchase' => $trade->id])"
            :label="__('Tagasi minu ostude juurde')"
            class="lg:hidden"
        />

        @include('user.purchases.partials.detail-panel', [
            'trade' => $trade,
        ])
    </div>
</x-layouts.app.public>