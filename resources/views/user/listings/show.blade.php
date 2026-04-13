<x-layouts.app.public :title="$listing->title">
    @php
        $isExpired = $listing->isExpired();
        $canEdit = $listing->canBeEditedByOwner();
        $canDelete = $listing->canBeDeletedByOwner();
        $canToggle = $listing->canBeToggledByOwner();
        $conversationUrl = $listing->conversationUrl();

        $reservedTrade = $listing->reservedTrade;
        $awaitingConfirmationTrade = $listing->awaitingConfirmationTrade;

        $activeTrade = $awaitingConfirmationTrade ?? $reservedTrade;

        $isReserved = $listing->status === 'reserved' && $reservedTrade;
        $isAwaitingConfirmation = $listing->status === 'reserved' && $awaitingConfirmationTrade;
    @endphp

    <div class="mx-auto max-w-5xl space-y-4 px-4 py-6 md:px-0">
        <a
            href="{{ route('listings.mine') }}"
            class="inline-flex items-center gap-2 text-sm text-zinc-600 transition hover:text-zinc-900 hover:underline"
        >
            <span>←</span>
            <span>{{ __('Tagasi minu kuulutuste juurde') }}</span>
        </a>

        <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.status-badge
                            :status="$listing->status"
                            :expired="$isExpired"
                            class="font-semibold"
                        >
                            {{ $listing->statusLabel() }}
                        </x-ui.status-badge>

                        @if($listing->statusHelpText())
                            <span class="text-sm text-zinc-600">
                                {{ $listing->statusHelpText() }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    @if($conversationUrl)
                        <a
                            href="{{ $conversationUrl }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100"
                        >
                            {{ __('Vestlus') }}
                        </a>
                    @endif

                    @if($canEdit)
                        <a
                            href="{{ route('listings.mine.edit', $listing) }}"
                            wire:navigate
                            class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
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
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto"
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
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto"
                            >
                                {{ __('Aktiveeri') }}
                            </button>
                        </form>
                    @endif

                    @if($listing->status === 'published' && !$isExpired && $canToggle)
                        <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 sm:w-auto"
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
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto"
                            >
                                {{ __('Uuesti müüki') }}
                            </button>
                        </form>
                    @endif

                    @if($isReserved)
                        <form method="POST" action="{{ route('messages.complete', $reservedTrade->conversation_id) }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto"
                            >
                                {{ __('Märgi üleantuks') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('messages.trades.cancel', [$reservedTrade->conversation_id, $reservedTrade]) }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:bg-amber-100 sm:w-auto"
                            >
                                {{ __('Tühista broneering') }}
                            </button>
                        </form>
                    @endif

                    @if($isAwaitingConfirmation)
                        <form method="POST" action="{{ route('messages.trades.cancel', [$awaitingConfirmationTrade->conversation_id, $awaitingConfirmationTrade]) }}">
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:bg-amber-100 sm:w-auto"
                            >
                                {{ __('Katkesta tehing') }}
                            </button>
                        </form>
                    @endif

                    @if($canDelete)
                        <form
                            method="POST"
                            action="{{ route('listings.mine.destroy', $listing) }}"
                            onsubmit="return confirm('{{ __('Kas oled kindel, et soovid kuulutuse kustutada?') }}')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 sm:w-auto"
                            >
                                {{ __('Kustuta') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <x-listings.detail :listing="$listing" />
    </div>
</x-layouts.app.public>