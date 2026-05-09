<x-layouts.app.public :title="__('Minule jäetud tagasiside')">
    @php
        $user = auth()->user();

        $averageRating = $user->averageRating();
        $reviewsTotal = $user->reviewsCount();

        $ratingLabel = match (true) {
            $averageRating >= 4.8 => __('Suurepärane'),
            $averageRating >= 4.3 => __('Väga hea'),
            $averageRating >= 3.5 => __('Hea'),
            $averageRating >= 2.5 => __('Rahuldav'),
            $averageRating > 0 => __('Nõrk'),
            default => __('Tagasiside puudub'),
        };
    @endphp

    <div class="mx-auto w-full max-w-[1500px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3">
            <x-ui.back-button :href="route('dashboard')" />

            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950">
                    {{ __('Minule jäetud tagasiside') }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-zinc-600">
                    {{ __('Siin näed hinnanguid ja kommentaare, mille teised kasutajad on sulle jätnud pärast tehinguid.') }}
                </p>
            </div>
        </div>

        @if($reviews->count())
            <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="self-start rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 ring-1 ring-amber-900/10">
                            <x-icons.star class="h-7 w-7 text-amber-500" />
                        </div>

                        <div>
                            <div class="text-4xl font-extrabold leading-none text-emerald-950">
                                {{ number_format($averageRating, 1, ',', ' ') }}
                            </div>

                            <div class="mt-1 text-sm font-bold text-zinc-500">
                                {{ __('keskmine hinnang') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-emerald-50/60 p-4 ring-1 ring-emerald-900/10">
                        <div class="text-base font-extrabold text-emerald-950">
                            {{ $ratingLabel }}
                        </div>

                        <div class="mt-1 text-sm font-medium text-zinc-600">
                            {{ trans_choice('Põhineb :count hinnangul|Põhineb :count hinnangul', $reviewsTotal, ['count' => $reviewsTotal]) }}
                        </div>
                    </div>
                </aside>

                <section class="space-y-4">
                    @foreach($reviews as $review)
                        <x-reviews.card :review="$review" />
                    @endforeach

                    <div class="pt-2">
                        {{ $reviews->links() }}
                    </div>
                </section>
            </div>
        @else
            <div class="rounded-[2rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center shadow-sm">
                <p class="text-base font-bold text-emerald-950">
                    {{ __('Sulle ei ole veel tagasisidet jäetud.') }}
                </p>

                <p class="mt-2 text-sm font-medium text-zinc-500">
                    {{ __('Tagasiside ilmub siia pärast lõpetatud tehinguid.') }}
                </p>
            </div>
        @endif
    </div>
</x-layouts.app.public>