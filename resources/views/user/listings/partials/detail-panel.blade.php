@php
    $isExpired = $listing->isExpired();
    $canEdit = $listing->canBeEditedByOwner();
    $canDelete = $listing->canBeDeletedByOwner();
    $canToggle = $listing->canBeToggledByOwner();

    $reservedTrade = $listing->reservedTrade;
    $awaitingConfirmationTrade = $listing->awaitingConfirmationTrade;
    $soldTrade = $listing->soldTrade;

    if (! $listing->relationLoaded('purchaseRequests')) {
        $listing->loadMissing([
            'purchaseRequests.buyer',
            'purchaseRequests.conversation',
        ]);
    }

    $purchaseRequests = $listing->purchaseRequests;
    $purchaseRequestsCount = $purchaseRequests->count();

    $singleInterestTrade = $listing->singleInterestTrade();

    $primaryRelatedTrade = $awaitingConfirmationTrade
        ?? $reservedTrade
        ?? $soldTrade
        ?? $singleInterestTrade;

    $primaryConversationUrl = $primaryRelatedTrade?->conversation
        ? route('messages.show', $primaryRelatedTrade->conversation)
        : $listing->conversationUrl();

    $primaryBuyerProfileUrl = $primaryRelatedTrade?->buyer
        ? route('users.show', $primaryRelatedTrade->buyer)
        : null;

    $reviewTrade = $soldTrade;

    $reviewMissing =
        $reviewTrade
        && auth()->check()
        && $reviewTrade->canBeReviewedBy(auth()->user())
        && ! $reviewTrade->hasReviewFrom(auth()->user());

    $statusHelpText = $listing->statusHelpText();

    $showTradeInfoNote =
        $primaryConversationUrl
        || $purchaseRequestsCount > 0
        || $reviewMissing
        || in_array($listing->status, ['reserved', 'sold'], true);

    $showPurchaseRequestsPanel =
        ! in_array($listing->status, ['draft', 'sold'], true)
        && $purchaseRequestsCount > 0;

    $deleteTitle = $listing->status === 'draft'
        ? __('Kustuta mustand?')
        : __('Kustuta kuulutus?');

    $deleteDescription = $listing->status === 'draft'
        ? __('Mustand kustutatakse jäädavalt koos piltidega.')
        : __('Kuulutus eemaldatakse sinu vaatest ja avalikust vaatest. Tehingute ja ostuajalooga seotud andmed säilivad süsteemis.');
@endphp

<div class="space-y-4">
    <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.status-badge
                        :status="$listing->status"
                        :expired="$isExpired"
                        class="font-semibold"
                    >
                        {{ $listing->statusLabel() }}
                    </x-ui.status-badge>

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

                @if($showTradeInfoNote)
                    <div class="mt-2 rounded-2xl bg-emerald-50/60 px-3 py-2 text-xs font-semibold leading-5 text-emerald-900 ring-1 ring-emerald-900/10">
                        @if($reviewMissing)
                            {{ __('Tagasiside on veel jätmata. Ava vestlus, et ostjale tagasiside jätta.') }}
                        @else
                            {{ __('Tehinguga seotud tegevused, nagu broneerimine, üleandmine, katkestamine, kinnitamine ja tagasiside jätmine, asuvad vastavas vestluses.') }}
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap xl:justify-end">
                @if($primaryConversationUrl)
                    <a
                        href="{{ $primaryConversationUrl }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100"
                    >
                        {{ __('Vestlus') }}
                    </a>
                @endif

                @if($primaryBuyerProfileUrl)
                    <a
                        href="{{ $primaryBuyerProfileUrl }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-4 py-2.5 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800"
                    >
                        {{ __('Ostja profiil') }}
                    </a>
                @endif

                @if($canEdit)
                    <a
                        href="{{ route('listings.mine.edit', [
                            'listing' => $listing,
                            'return_to' => request()->fullUrl(),
                        ]) }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-4 py-2.5 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800"
                    >
                        {{ __('Muuda') }}
                    </a>
                @endif

                @if($listing->status === 'draft')
                    <form method="POST" action="{{ route('listings.mine.publish', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-emerald-800 sm:w-auto"
                        >
                            {{ __('Avalda') }}
                        </button>
                    </form>
                @endif

                @if($listing->status === 'archived' && $canToggle)
                    <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-emerald-800 sm:w-auto"
                        >
                            {{ __('Aktiveeri') }}
                        </button>
                    </form>
                @endif

                @if($listing->status === 'published' && ! $isExpired && $canToggle)
                    <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-4 py-2.5 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800 sm:w-auto"
                        >
                            {{ __('Peata') }}
                        </button>
                    </form>
                @endif

                @if($isExpired)
                    <form method="POST" action="{{ route('listings.mine.relist', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-900 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-emerald-800 sm:w-auto"
                        >
                            {{ __('Uuesti müüki') }}
                        </button>
                    </form>
                @endif

                @if($canDelete)
                    <div x-data="{ deleteListingModalOpen: false }">
                        <form
                            x-ref="deleteListingForm"
                            method="POST"
                            action="{{ route('listings.mine.destroy', $listing) }}"
                            class="hidden"
                        >
                            @csrf
                            @method('DELETE')
                        </form>

                        <button
                            type="button"
                            @click="deleteListingModalOpen = true"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-extrabold text-red-700 transition hover:bg-red-100 sm:w-auto"
                        >
                            {{ __('Kustuta') }}
                        </button>

                        <x-ui.confirm-modal
                            open="deleteListingModalOpen"
                            :title="$deleteTitle"
                            :description="$deleteDescription"
                            :cancel-text="__('Tühista')"
                            :confirm-text="__('Kustuta')"
                            confirm-click="$refs.deleteListingForm.submit()"
                        />
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($showPurchaseRequestsPanel)
        <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white p-4 shadow-sm">
            <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-emerald-950">
                        {{ __('Ostusoovid') }}
                    </h2>

                    <p class="text-sm font-medium text-zinc-500">
                        {{ __('Kõik selle kuulutuse ostuhuvid ja aktiivsed ostuprotsessid. Tegevuste tegemiseks ava vastav vestlus.') }}
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full bg-emerald-900 px-3 py-1 text-xs font-extrabold text-white">
                    {{ __('Ostusoove: :count', ['count' => $purchaseRequestsCount]) }}
                </span>
            </div>

            <div class="space-y-2">
                @foreach($purchaseRequests as $trade)
                    @php
                        $buyerName = $trade->buyer?->name ?: __('Kasutaja');

                        $tradeStatusLabel = match ($trade->status) {
                            'interest' => __('Ostusoov'),
                            'reserved' => __('Broneeritud'),
                            'awaiting_confirmation' => __('Ootab kinnitust'),
                            default => __('—'),
                        };

                        $tradeStatusClasses = match ($trade->status) {
                            'interest' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                            'reserved' => 'border-amber-200 bg-amber-50 text-amber-800',
                            'awaiting_confirmation' => 'border-violet-200 bg-violet-50 text-violet-800',
                            default => 'border-zinc-200 bg-zinc-50 text-zinc-700',
                        };

                        $buyerProfileUrl = $trade->buyer
                            ? route('users.show', $trade->buyer)
                            : null;
                    @endphp

                    <div class="rounded-2xl border border-emerald-950/10 bg-white p-3 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="truncate text-sm font-extrabold text-emerald-950">
                                        {{ $buyerName }}
                                    </div>

                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $tradeStatusClasses }}">
                                        {{ $tradeStatusLabel }}
                                    </span>
                                </div>

                                <div class="mt-1 text-xs font-medium text-zinc-500">
                                    {{ __('Esitatud') }}:
                                    {{ $trade->created_at?->format('d.m.Y H:i') ?? '—' }}
                                </div>
                            </div>

                            <div class="grid gap-2 sm:flex sm:items-center sm:justify-end">
                                @if($trade->conversation)
                                    <a
                                        href="{{ route('messages.show', $trade->conversation) }}"
                                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-3 py-2 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800"
                                    >
                                        {{ __('Vestlus') }}
                                    </a>
                                @endif

                                @if($buyerProfileUrl)
                                    <a
                                        href="{{ $buyerProfileUrl }}"
                                        class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-3 py-2 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50 hover:text-emerald-800"
                                    >
                                        {{ __('Ostja profiil') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-listings.detail :listing="$listing" />
</div>