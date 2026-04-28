@props([
    'trade',
])

@php
    $listing = $trade->listing;
    $conversation = $trade->conversation;

    $conversationVisible =
        $conversation
        && auth()->check()
        && ! $conversation->isHiddenFor(auth()->user());

    $statusLabel = match ($trade->status) {
        'interest' => __('Ostusoov'),
        'reserved' => __('Broneeritud'),
        'awaiting_confirmation' => __('Ootan kinnitust'),
        'completed' => __('Lõpetatud'),
        'cancelled' => __('Katkestatud'),
        default => __('—'),
    };

    $statusClasses = match ($trade->status) {
        'interest' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'reserved' => 'border-amber-200 bg-amber-50 text-amber-800',
        'awaiting_confirmation' => 'border-violet-200 bg-violet-50 text-violet-800',
        'completed' => 'border-emerald-900 bg-emerald-900 text-white',
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

    $reviewMissing =
        $conversationVisible
        && auth()->check()
        && $trade->canBeReviewedBy(auth()->user())
        && ! $trade->hasReviewFrom(auth()->user());

    $canHidePurchase =
        \Illuminate\Support\Facades\Route::has('purchases.hide')
        && auth()->check()
        && (int) auth()->id() === (int) $trade->buyer_id
        && method_exists($trade, 'canBeHiddenByBuyer')
        && $trade->canBeHiddenByBuyer();

    $sellerProfileUrl = $trade->seller ? route('users.show', $trade->seller) : null;

    $deleteTitle = __('Kustuta ost?');

    $deleteDescription = __('Ost eemaldatakse sinu ostude vaatest. Tehingu ja vestlusega seotud andmed säilivad süsteemis.');
@endphp

<div class="space-y-4">
    <div class="rounded-3xl border border-emerald-950/10 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>

                    @if($statusHelpText)
                        <span class="text-sm font-medium text-zinc-600">
                            {{ $statusHelpText }}
                        </span>
                    @endif

                    @if($reviewMissing)
                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">
                            {{ __('Jäta tagasiside vestluses') }}
                        </span>
                    @endif
                </div>

                <div class="mt-2 rounded-2xl bg-emerald-50/60 px-3 py-2 text-xs font-semibold leading-5 text-emerald-900 ring-1 ring-emerald-900/10">
                    @if($reviewMissing)
                        {{ __('Tagasiside on veel jätmata. Ava vestlus, et müüjale tagasiside jätta.') }}
                    @else
                        {{ __('Tehinguga seotud tegevused, nagu kauba kättesaamise kinnitamine, tehingu katkestamine ja tagasiside jätmine, asuvad vestluses.') }}
                    @endif
                </div>
            </div>

            <div
                x-data="{
                    deletePurchaseModalOpen: false
                }"
                class="flex flex-col gap-2 sm:flex-row sm:flex-wrap xl:justify-end"
            >
                @if($conversationVisible)
                    <a
                        href="{{ route('messages.show', $conversation) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100"
                    >
                        {{ __('Vestlus') }}
                    </a>
                @endif

                @if($sellerProfileUrl)
                    <a
                        href="{{ $sellerProfileUrl }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-4 py-2.5 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800"
                    >
                        {{ __('Müüja profiil') }}
                    </a>
                @endif

                @if($canHidePurchase)
                    <form
                        x-ref="deletePurchaseForm"
                        method="POST"
                        action="{{ route('purchases.hide', $trade) }}"
                        class="hidden"
                    >
                        @csrf
                        @method('PATCH')
                    </form>

                    <button
                        type="button"
                        @click="deletePurchaseModalOpen = true"
                        class="inline-flex w-full items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-extrabold text-red-700 transition hover:bg-red-100 sm:w-auto"
                    >
                        {{ __('Kustuta') }}
                    </button>

                    <x-ui.confirm-modal
                        open="deletePurchaseModalOpen"
                        :title="$deleteTitle"
                        :description="$deleteDescription"
                        :cancel-text="__('Tühista')"
                        :confirm-text="__('Kustuta')"
                        confirm-click="$refs.deletePurchaseForm.submit()"
                    />
                @endif
            </div>
        </div>
    </div>

    @if($listing)
        <x-listings.detail :listing="$listing" />
    @else
        <div class="rounded-[2rem] border border-dashed border-emerald-950/15 bg-white p-8 text-center shadow-sm">
            <p class="text-base font-bold text-emerald-950">
                {{ __('Kuulutus on eemaldatud') }}
            </p>

            <p class="mt-2 text-sm font-medium text-zinc-500">
                {{ __('Selle ostuga seotud kuulutust ei saa enam kuvada.') }}
            </p>
        </div>
    @endif
</div>