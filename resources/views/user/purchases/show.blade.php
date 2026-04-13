<x-layouts.app.public :title="$listing->title">
    @php
        $conversation = $trade->conversation;

        $statusLabel = match ($trade->status) {
            'interest' => __('Ostusoov'),
            'reserved' => __('Broneeritud'),
            'awaiting_confirmation' => __('Ootan kinnitust'),
            'completed' => __('Lõpetatud'),
            'cancelled' => __('Katkestatud'),
            default => __('—'),
        };

        $statusClasses = match ($trade->status) {
            'interest' => 'border-sky-200 bg-sky-100 text-sky-800',
            'reserved' => 'border-amber-200 bg-amber-100 text-amber-800',
            'awaiting_confirmation' => 'border-violet-200 bg-violet-100 text-violet-800',
            'completed' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
            'cancelled' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
            default => 'border-zinc-200 bg-zinc-100 text-zinc-700',
        };

        $statusHelpText = match ($trade->status) {
            'interest' => __('Ostusoov on esitatud müüjale.'),
            'reserved' => __('Kuulutus on sulle broneeritud.'),
            'awaiting_confirmation' => __('Müüja märkis kauba üleantuks või teele panduks. Oodatakse sinu kinnitust.'),
            'completed' => __('Tehing on täielikult lõpetatud.'),
            'cancelled' => __('See tehing on katkestatud.'),
            default => null,
        };

        $canConfirm =
            $conversation
            && $trade->canBeConfirmedByBuyer()
            && auth()->check()
            && auth()->id() === $trade->buyer_id;

        $canReview =
            $conversation
            && auth()->check()
            && $trade->canBeReviewedBy(auth()->user())
            && ! $trade->hasReviewFrom(auth()->user());

        $sellerProfileUrl = route('users.show', $trade->seller);
    @endphp

    <div class="mx-auto max-w-5xl space-y-4 px-4 py-6 md:px-0">
        <a
            href="{{ route('purchases.index') }}"
            class="inline-flex items-center gap-2 text-sm text-zinc-600 transition hover:text-zinc-900 hover:underline"
        >
            <span>←</span>
            <span>{{ __('Tagasi minu ostude juurde') }}</span>
        </a>

        <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold whitespace-nowrap {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>

                        @if($statusHelpText)
                            <span class="text-sm text-zinc-600">
                                {{ $statusHelpText }}
                            </span>
                        @endif
                    </div>
                </div>

                <div x-data="{ showReviewModal: false }" class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    @if($conversation && auth()->check() && ! $conversation->isHiddenFor(auth()->user()))
                        <a
                            href="{{ route('messages.show', $conversation) }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100"
                        >
                            {{ __('Vestlus') }}
                        </a>
                    @endif

                    <a
                        href="{{ $sellerProfileUrl }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                    >
                        {{ __('Müüja profiil') }}
                    </a>

                    @if($canConfirm)
                        <form method="POST" action="{{ route('messages.trades.confirm', $conversation) }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto"
                            >
                                {{ __('Kinnita kauba kättesaamine') }}
                            </button>
                        </form>
                    @endif

                    @if($canReview)
                        <button
                            type="button"
                            @click="showReviewModal = true"
                            class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                        >
                            {{ __('Jäta tagasiside') }}
                        </button>

                        <x-reviews.create-modal
                            :conversation="$conversation"
                            :trade="$trade"
                            open-state="showReviewModal"
                            :title="__('Jäta tagasiside')"
                            :description="__('Anna hinnang müüjale ja lisa soovi korral kommentaar.')"
                        />
                    @endif
                </div>
            </div>
        </div>

        <x-listings.detail :listing="$listing" />
    </div>
</x-layouts.app.public>