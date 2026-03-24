@props([
    // Vestluse teine osapool
    'user',

    // Valikuline rollimärgis, nt "Müüja" / "Ostja"
    'roleLabel' => null,

    // Tulevikuks hinnangu keskmine
    'score' => null,

    // Tulevikuks hinnangute arv
    'reviewsCount' => null,

    // Route vestluse peitmiseks
    'hideConversationAction' => null,

    // Route kasutaja blokeerimiseks
    'blockUserAction' => null,

    // Route blokeeringu eemaldamiseks
    'unblockUserAction' => null,

    // Kas mina olen selle kasutaja blokeerinud
    'isBlockedByMe' => false,

    // Kas kasutajate vahel on ükskõik kummas suunas sõnumiblokk
    'hasMessagingBlock' => false,

    // Route kasutajast teatamiseks
    'reportUserAction' => null,

    // Aktiivse vestluse id reporti sidumiseks
    'conversationId' => null,
])

@php
    $joinedYear = optional($user->created_at)?->format('Y');

    $blockConfirmClick = $blockUserAction
        ? "\$root.querySelector('form[data-block-user-form]')?.requestSubmit()"
        : null;

    $unblockConfirmClick = $unblockUserAction
        ? "\$root.querySelector('form[data-unblock-user-form]')?.requestSubmit()"
        : null;

    // Kui report-vormis oli valideerimisviga, avame report-modali automaatselt uuesti
    $hasReportErrors =
        $errors->has('reported_user_id') ||
        $errors->has('conversation_id') ||
        $errors->has('reason') ||
        $errors->has('details');
@endphp

<div
    x-data="{
        openMenu: false,
        showHideModal: false,
        showBlockModal: false,
        showUnblockModal: false,
        showReportModal: {{ $hasReportErrors ? 'true' : 'false' }},
    }"
    @keydown.escape.window="
        openMenu = false;
        showHideModal = false;
        showBlockModal = false;
        showUnblockModal = false;
        showReportModal = false;
    "
    class="relative rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm"
>
    <div class="flex items-start gap-4">
        {{-- Profiilipilt või initsiaalid --}}
        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-100">
            @if(!empty($user->profile_photo_url))
                <img
                    src="{{ $user->profile_photo_url }}"
                    alt="{{ $user->name }}"
                    class="h-full w-full object-cover"
                >
            @else
                <span class="text-sm font-semibold text-zinc-600">
                    {{ $user->initials() }}
                </span>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <h3 class="text-base font-semibold text-zinc-900">
                            {{ $user->name ?? __('Kasutaja') }}
                        </h3>

                        @if($roleLabel)
                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600">
                                {{ $roleLabel }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-1 text-sm text-zinc-500">
                        @if($joinedYear)
                            {{ __('Kasutaja alates :year', ['year' => $joinedYear]) }}
                        @else
                            {{ __('Kasutaja') }}
                        @endif
                    </div>

                    @if($isBlockedByMe)
                        <div class="mt-3 inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">
                            {{ __('Oled selle kasutaja blokeerinud') }}
                        </div>
                    @elseif($hasMessagingBlock)
                        <div class="mt-3 inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700">
                            {{ __('Selle kasutajaga ei saa praegu uusi sõnumeid vahetada') }}
                        </div>
                    @endif
                </div>

                <div class="relative shrink-0" @click.outside="openMenu = false">
                    <button
                        type="button"
                        @click="openMenu = !openMenu"
                        :aria-expanded="openMenu.toString()"
                        aria-haspopup="true"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-transparent text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 focus:outline-none focus:ring-2 focus:ring-green-500/30"
                        title="{{ __('Vestluse valikud') }}"
                    >
                        <x-icons.dots-vertical class="h-5 w-5" />
                    </button>

                    <div
                        x-cloak
                        x-show="openMenu"
                        x-transition.origin.top.right
                        class="absolute right-0 top-12 z-30 w-60 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-lg"
                    >
                        <div class="py-1">
                            <x-ui.dropdown-item
                                icon="icons.user"
                                @click="openMenu = false"
                            >
                                {{ __('Näita profiili') }}
                            </x-ui.dropdown-item>
                        </div>

                        <div class="my-1 border-t border-zinc-100"></div>

                        <div class="py-1">
                            @if($hideConversationAction)
                                <form
                                    method="POST"
                                    action="{{ $hideConversationAction }}"
                                    data-hide-conversation-form
                                >
                                    @csrf
                                    @method('DELETE')

                                    <x-ui.dropdown-item
                                        icon="icons.archive"
                                        @click.prevent="openMenu = false; showHideModal = true"
                                    >
                                        {{ __('Eemalda vestlus') }}
                                    </x-ui.dropdown-item>
                                </form>
                            @endif

                            @if($isBlockedByMe && $unblockUserAction)
                                <form
                                    method="POST"
                                    action="{{ $unblockUserAction }}"
                                    data-unblock-user-form
                                >
                                    @csrf
                                    @method('DELETE')

                                    <x-ui.dropdown-item
                                        icon="icons.block"
                                        danger
                                        @click.prevent="openMenu = false; showUnblockModal = true"
                                    >
                                        {{ __('Eemalda blokeering') }}
                                    </x-ui.dropdown-item>
                                </form>
                            @elseif($blockUserAction)
                                <form
                                    method="POST"
                                    action="{{ $blockUserAction }}"
                                    data-block-user-form
                                >
                                    @csrf

                                    <x-ui.dropdown-item
                                        icon="icons.block"
                                        danger
                                        @click.prevent="openMenu = false; showBlockModal = true"
                                    >
                                        {{ __('Blokeeri kasutaja') }}
                                    </x-ui.dropdown-item>
                                </form>
                            @else
                                <x-ui.dropdown-item
                                    icon="icons.block"
                                    danger
                                    @click="openMenu = false; showBlockModal = true"
                                >
                                    {{ __('Blokeeri kasutaja') }}
                                </x-ui.dropdown-item>
                            @endif
                        </div>

                        <div class="my-1 border-t border-zinc-100"></div>

                        <div class="py-1">
                            <x-ui.dropdown-item
                                icon="icons.report"
                                danger
                                @click="openMenu = false; showReportModal = true"
                            >
                                {{ __('Teata kasutajast') }}
                            </x-ui.dropdown-item>
                        </div>
                    </div>
                </div>
            </div>

            @if(!is_null($score) || !is_null($reviewsCount))
                <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                    @if(!is_null($score))
                        <span class="font-semibold text-zinc-900">
                            {{ number_format((float) $score, 1, ',', ' ') }}
                        </span>
                    @endif

                    @if(!is_null($reviewsCount))
                        <span class="text-zinc-500">
                            {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCount, ['count' => $reviewsCount]) }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if($hideConversationAction)
        <x-ui.confirm-modal
            open="showHideModal"
            :title="__('Eemalda see vestlus?')"
            :description="__('Vestlus eemaldatakse sinu vaatest. Kui teine kasutaja saadab hiljem uue sõnumi, muutub vestlus uuesti nähtavaks.')"
            icon="icons.archive"
            :confirm-text="__('Eemalda vestlus')"
            confirm-click="$root.querySelector('form[data-hide-conversation-form]')?.requestSubmit()"
        />
    @endif

    @if(!$isBlockedByMe)
        <x-ui.confirm-modal
            open="showBlockModal"
            :title="__('Blokeerida see kasutaja?')"
            :description="__('Pärast blokeerimist ei saa teie vahel enam uusi sõnumeid saata.')"
            icon="icons.block"
            icon-wrapper-class="bg-red-50"
            icon-class="text-red-600"
            :confirm-text="__('Blokeeri kasutaja')"
            confirm-button-class="bg-red-600 text-white hover:bg-red-700"
            :confirm-click="$blockConfirmClick"
        />
    @endif

    @if($isBlockedByMe && $unblockUserAction)
        <x-ui.confirm-modal
            open="showUnblockModal"
            :title="__('Eemaldada blokeering?')"
            :description="__('Pärast blokeeringu eemaldamist saate selle kasutajaga jälle uusi sõnumeid vahetada.')"
            icon="icons.block"
            icon-wrapper-class="bg-zinc-100"
            icon-class="text-zinc-700"
            :confirm-text="__('Eemalda blokeering')"
            confirm-button-class="bg-zinc-900 text-white hover:bg-zinc-800"
            :confirm-click="$unblockConfirmClick"
        />
    @endif

    @if($reportUserAction)
        <x-ui.report-modal
            open="showReportModal"
            :action="$reportUserAction"
            :reported-user-id="$user->id"
            :conversation-id="$conversationId"
        />
    @endif
</div>