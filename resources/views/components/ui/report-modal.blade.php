@props([
    // Alpine muutuja nimi, mis juhib modali nähtavust
    'open' => 'false',

    // Vorm action
    'action' => null,

    // Kelle kohta report tehakse
    'reportedUserId',

    // Millise vestluse kontekstis report tehakse
    'conversationId' => null,

    // Modali pealkiri
    'title' => __('Teata kasutajast'),

    // Selgitav tekst
    'description' => __('Vali põhjus, miks soovid sellest kasutajast teada anda. Teade salvestatakse ülevaatamiseks.'),
])

<div
    x-cloak
    x-show="{{ $open }}"
    x-transition.opacity
    class="fixed inset-0 z-40 flex items-center justify-center bg-zinc-950/50 p-4"
>
    <div
        @click.outside="{{ $open }} = false"
        @click.stop
        x-transition
        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
    >
        <form method="POST" action="{{ $action }}">
            @csrf

            {{-- Peidetud väljad reporti sidumiseks --}}
            <input type="hidden" name="reported_user_id" value="{{ $reportedUserId }}">
            <input type="hidden" name="conversation_id" value="{{ $conversationId }}">

            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50">
                    <x-icons.report class="h-5 w-5 text-red-600" />
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-semibold text-zinc-900">
                        {{ $title }}
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-zinc-600">
                        {{ $description }}
                    </p>

                    {{-- Põhjuse valik --}}
                    <div class="mt-4 space-y-2">
                        <label class="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 text-sm">
                            <input
                                type="radio"
                                name="reason"
                                value="spam"
                                class="h-4 w-4 border-zinc-300 text-green-600 focus:ring-green-500"
                                {{ old('reason') === 'spam' ? 'checked' : '' }}
                            >
                            <span class="text-zinc-700">{{ __('Spämm') }}</span>
                        </label>

                        <label class="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 text-sm">
                            <input
                                type="radio"
                                name="reason"
                                value="scam"
                                class="h-4 w-4 border-zinc-300 text-green-600 focus:ring-green-500"
                                {{ old('reason') === 'scam' ? 'checked' : '' }}
                            >
                            <span class="text-zinc-700">{{ __('Petuskeemi kahtlus') }}</span>
                        </label>

                        <label class="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 text-sm">
                            <input
                                type="radio"
                                name="reason"
                                value="inappropriate"
                                class="h-4 w-4 border-zinc-300 text-green-600 focus:ring-green-500"
                                {{ old('reason') === 'inappropriate' ? 'checked' : '' }}
                            >
                            <span class="text-zinc-700">{{ __('Sobimatu suhtlus') }}</span>
                        </label>

                        <label class="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 text-sm">
                            <input
                                type="radio"
                                name="reason"
                                value="harassment"
                                class="h-4 w-4 border-zinc-300 text-green-600 focus:ring-green-500"
                                {{ old('reason') === 'harassment' ? 'checked' : '' }}
                            >
                            <span class="text-zinc-700">{{ __('Ahistamine') }}</span>
                        </label>

                        <label class="flex items-center gap-3 rounded-xl border border-zinc-200 px-3 py-2.5 text-sm">
                            <input
                                type="radio"
                                name="reason"
                                value="other"
                                class="h-4 w-4 border-zinc-300 text-green-600 focus:ring-green-500"
                                {{ old('reason') === 'other' ? 'checked' : '' }}
                            >
                            <span class="text-zinc-700">{{ __('Muu põhjus') }}</span>
                        </label>
                    </div>

                    {{-- Lisaselgitus --}}
                    <textarea
                        name="details"
                        rows="4"
                        placeholder="{{ __('Lisa lühike selgitus...') }}"
                        class="mt-4 w-full rounded-xl border border-zinc-200 px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20"
                    >{{ old('details') }}</textarea>

                    {{-- Veateated --}}
                    @error('reason')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @error('details')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    @click="{{ $open }} = false"
                    class="inline-flex items-center rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                >
                    {{ __('Tühista') }}
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                >
                    {{ __('Saada teade') }}
                </button>
            </div>
        </form>
    </div>
</div>