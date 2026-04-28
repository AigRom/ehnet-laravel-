@props([
    'user',
    'roleLabel' => null,
    'hideConversationAction' => null,
    'blockUserAction' => null,
    'unblockUserAction' => null,
    'isBlockedByMe' => false,
    'hasMessagingBlock' => false,
    'reportUserAction' => null,
    'conversationId' => null,
    'locationLabel' => null,
])

@php
    $joinedYear = optional($user->created_at)?->format('Y');
    $profileUrl = route('users.show', $user);

    $blockConfirmClick = $blockUserAction
        ? "\$root.querySelector('form[data-block-user-form]')?.requestSubmit()"
        : null;

    $unblockConfirmClick = $unblockUserAction
        ? "\$root.querySelector('form[data-unblock-user-form]')?.requestSubmit()"
        : null;

    $hasReportErrors =
        $errors->has('reported_user_id') ||
        $errors->has('conversation_id') ||
        $errors->has('reason') ||
        $errors->has('details');

    $hasMeta = filled($joinedYear) || filled($locationLabel);

    $reviewsCountValue = $user->reviewsCount();
    $scoreValue = $user->averageRating();
    $hasReviews = $user->hasReviews();
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
    class="relative"
>
    <div class="relative rounded-2xl border border-emerald-950/10 bg-white px-3 py-3 shadow-sm sm:px-4 sm:py-4">
        <div class="absolute right-3 top-3 z-10" @click.outside="openMenu = false">
            <button
                type="button"
                @click="openMenu = !openMenu"
                :aria-expanded="openMenu.toString()"
                aria-haspopup="true"
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-950/10 bg-white text-zinc-500 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                title="{{ __('Vestluse valikud') }}"
                aria-label="{{ __('Vestluse valikud') }}"
            >
                <x-icons.dots-vertical class="h-6 w-6" />
            </button>

            <div
                x-cloak
                x-show="openMenu"
                x-transition.origin.top.right
                class="absolute right-0 top-12 z-30 w-60 overflow-hidden rounded-2xl border border-emerald-950/10 bg-white p-1.5 shadow-xl shadow-emerald-950/10"
            >
                <div class="py-1">
                    <a
                        href="{{ $profileUrl }}"
                        class="block"
                        @click="openMenu = false"
                    >
                        <x-ui.dropdown-item icon="icons.user">
                            {{ __('Näita profiili') }}
                        </x-ui.dropdown-item>
                    </a>
                </div>

                <div class="my-1 border-t border-emerald-950/10"></div>

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
                    @elseif(!$isBlockedByMe && $blockUserAction)
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
                    @endif
                </div>

                <div class="my-1 border-t border-emerald-950/10"></div>

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

        <div class="flex items-start gap-3 pr-14">
            <x-ui.avatar :user="$user" size="h-12 w-12 sm:h-14 sm:w-14" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <h3 class="text-base font-extrabold text-emerald-950 sm:text-lg">
                        {{ $user->name ?? __('Kasutaja') }}
                    </h3>

                    @if($roleLabel)
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-900 sm:text-xs">
                            {{ $roleLabel }}
                        </span>
                    @endif
                </div>

                @if($hasMeta)
                    <div class="mt-1 space-y-0.5 text-xs font-medium text-zinc-500 sm:text-sm">
                        @if($joinedYear)
                            <div>
                                {{ __('Kasutaja alates :year', ['year' => $joinedYear]) }}
                            </div>
                        @endif

                        @if($locationLabel)
                            <div>
                                {{ $locationLabel }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs sm:text-sm">
                    @if($hasReviews)
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-950">
                            <x-icons.star class="h-4 w-4 text-amber-500" />

                            {{ number_format($scoreValue, 1, ',', ' ') }}
                        </span>

                        <span class="font-medium text-zinc-500">
                            {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCountValue, ['count' => $reviewsCountValue]) }}
                        </span>
                    @else
                        <span class="font-medium text-zinc-500">
                            {{ __('Tagasiside puudub') }}
                        </span>
                    @endif
                </div>

                @if($isBlockedByMe || $hasMessagingBlock)
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @if($isBlockedByMe)
                            <div class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-700">
                                {{ __('Oled selle kasutaja blokeerinud') }}
                            </div>
                        @endif

                        @if($hasMessagingBlock)
                            <div class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-bold text-zinc-700">
                                {{ __('Selle kasutajaga ei saa praegu uusi sõnumeid vahetada') }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
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

    @if(!$isBlockedByMe && $blockUserAction)
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