@props([
    // Vestluse teine osapool
    'user',

    // Valikuline rollimärgis, nt "Müüja" / "Ostja"
    'roleLabel' => null,

    // Tulevikuks hinnangu keskmine
    'score' => null,

    // Tulevikuks hinnangute arv
    'reviewsCount' => null,

    // Route vestluse peitmiseks.
    // Antakse kaasa ainult seal, kus kaart asub päris vestluse vaates.
    'hideConversationAction' => null,
])

@php
    // Kuvame "Kasutaja alates 2025" formaadis ainult aasta
    $joinedYear = optional($user->created_at)?->format('Y');
@endphp

<div
    x-data="{
        // 3-punkti menüü nähtavus
        openMenu: false,

        // Modaalide nähtavus
        showHideModal: false,
        showBlockModal: false,
        showReportModal: false,
    }"
    @keydown.escape.window="
        // ESC sulgeb nii menüü kui kõik avatud modaalid
        openMenu = false;
        showHideModal = false;
        showBlockModal = false;
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
                {{-- Kasutaja põhiinfo --}}
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
                </div>

                {{-- 3-punkti menüü nupp --}}
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

                    {{-- Rippmenüü --}}
                    <div
                        x-cloak
                        x-show="openMenu"
                        x-transition.origin.top.right
                        class="absolute right-0 top-12 z-30 w-60 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-lg"
                    >
                        {{-- Profiili vaatamise rida --}}
                        <div class="py-1">
                            <x-ui.dropdown-item
                                icon="icons.user"
                                @click="openMenu = false"
                            >
                                {{ __('Näita profiili') }}
                            </x-ui.dropdown-item>
                        </div>

                        <div class="my-1 border-t border-zinc-100"></div>

                        {{-- Vestlusega seotud tegevused --}}
                        <div class="py-1">
                            @if($hideConversationAction)
                                {{-- Vorm päris route'ile.
                                     Klikk menüüreal ei saada kohe vormi ära,
                                     vaid avab enne kinnituse modaali. --}}
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

                            {{-- Blokeerimine on hetkel UI-prototüüp --}}
                            <x-ui.dropdown-item
                                icon="icons.block"
                                danger
                                @click="openMenu = false; showBlockModal = true"
                            >
                                {{ __('Blokeeri kasutaja') }}
                            </x-ui.dropdown-item>
                        </div>

                        <div class="my-1 border-t border-zinc-100"></div>

                        {{-- Raporteerimine on hetkel UI-prototüüp --}}
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

            {{-- Tulevikuks hinnangute info --}}
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

    {{-- Vestluse peitmise kinnituse modal.
         Kui kasutaja kinnitab, saadame ülal oleva DELETE vormi ära. --}}
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

    {{-- Blokeerimise modal on praegu ainult UI-taseme prototüüp --}}
    <x-ui.confirm-modal
        open="showBlockModal"
        :title="__('Blokeerida see kasutaja?')"
        :description="__('Pärast blokeerimist ei saa see kasutaja sulle uusi sõnumeid saata. Vajadusel saad blokeeringu hiljem eemaldada.')"
        icon="icons.block"
        icon-wrapper-class="bg-red-50"
        icon-class="text-red-600"
        :confirm-text="__('Blokeeri kasutaja')"
        confirm-button-class="bg-red-600 text-white hover:bg-red-700"
    >
        <label class="flex items-start gap-3 rounded-xl bg-zinc-50 px-3 py-3 text-sm">
            <input
                type="checkbox"
                class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-green-600 focus:ring-green-500"
            >
            <span class="text-zinc-700">
                {{ __('Soovin sellest kasutajast ka EHNETile teada anda.') }}
            </span>
        </label>
    </x-ui.confirm-modal>

    {{-- Raporteerimise modal on samuti praegu UI-prototüüp --}}
    <x-ui.report-modal
        open="showReportModal"
        :radio-name="'report_reason_'.$user->id"
    />
</div>