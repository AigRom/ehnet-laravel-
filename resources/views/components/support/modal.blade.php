@php
    $supportCategories = [
        'problem' => __('Probleem'),
        'listing' => __('Kuulutus'),
        'account' => __('Konto'),
        'feedback' => __('Tagasiside'),
        'suggestion' => __('Ettepanek'),
        'general' => __('Muu'),
    ];

    $supportErrorFields = [
        'name',
        'email',
        'category',
        'subject',
        'message',
        'website',
    ];

    $shouldOpenSupportModal = collect($supportErrorFields)
        ->contains(fn ($field) => $errors->has($field));
@endphp

<div
    x-data="supportModal({
        initialOpen: @js($shouldOpenSupportModal),
        initialCategory: @js(old('category', 'problem'))
    })"
>
    <button
        type="button"
        @click="openModal()"
        class="block text-left text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base"
    >
        {{ __('Abi ja tagasiside') }}
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            x-transition.opacity
            @keydown.escape.window="closeModal()"
            class="support-modal-root text-emerald-950"
            role="dialog"
            aria-modal="true"
            aria-labelledby="support-modal-title"
        >
            <div
                class="support-modal-backdrop"
                @click="closeModal()"
                aria-hidden="true"
            ></div>

            <div
                x-show="open"
                x-transition.opacity.duration.150ms
                @click.stop
                class="support-modal-panel"
            >
                <div class="shrink-0 border-b border-emerald-950/10 bg-emerald-50/70 px-4 py-4 sm:px-7 sm:py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="mb-1.5 inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-900 ring-1 ring-emerald-900/10 sm:mb-2 sm:text-xs">
                                {{ __('EHNET tugi') }}
                            </div>

                            <h2 id="support-modal-title" class="text-xl font-extrabold tracking-tight text-emerald-950 sm:text-2xl">
                                {{ __('Abi ja tagasiside') }}
                            </h2>

                            <p class="mt-1 text-sm font-medium leading-5 text-zinc-600 sm:mt-2 sm:leading-6">
                                {{ __('Kirjuta meile küsimus, probleem või parendusettepanek.') }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeModal()"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-emerald-950/10 bg-white text-zinc-500 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-900/10 sm:h-11 sm:w-11"
                            aria-label="{{ __('Sulge') }}"
                            title="{{ __('Sulge') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('support.store') }}"
                    novalidate
                    class="flex min-h-0 flex-1 flex-col"
                >
                    @csrf

                    <div class="support-modal-scroll px-4 py-4 sm:px-7 sm:py-6">
                        <div class="space-y-4 sm:space-y-6">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-emerald-950 sm:mb-3">
                                    {{ __('Vali teema') }}
                                </label>

                                <input type="hidden" name="category" x-model="category">

                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    @foreach($supportCategories as $value => $label)
                                        <button
                                            type="button"
                                            @click="category = @js($value)"
                                            :class="category === @js($value)
                                                ? 'border-emerald-900/20 bg-emerald-900 text-white shadow-lg shadow-emerald-950/15'
                                                : 'border-emerald-950/10 bg-stone-50 text-emerald-950 hover:bg-emerald-50 hover:text-emerald-900'"
                                            class="inline-flex items-center justify-center rounded-2xl border px-3 py-2.5 text-sm font-bold transition focus:outline-none focus:ring-4 focus:ring-emerald-900/10 sm:py-3"
                                        >
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>

                                @error('category')
                                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @guest
                                <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                                    <div>
                                        <label for="support_name" class="mb-2 block text-sm font-bold text-emerald-950 sm:mb-3">
                                            {{ __('Nimi') }}
                                            <span class="font-medium text-zinc-500">({{ __('valikuline') }})</span>
                                        </label>

                                        <input
                                            id="support_name"
                                            type="text"
                                            name="name"
                                            value="{{ old('name') }}"
                                            maxlength="100"
                                            class="w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 sm:rounded-3xl"
                                            placeholder="{{ __('Sinu nimi') }}"
                                        >

                                        @error('name')
                                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="support_email" class="mb-2 block text-sm font-bold text-emerald-950 sm:mb-3">
                                            {{ __('E-post') }}
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <input
                                            id="support_email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            maxlength="255"
                                            required
                                            class="w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 sm:rounded-3xl"
                                            placeholder="{{ __('nimi@email.ee') }}"
                                        >

                                        @error('email')
                                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @else
                                <div class="rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 sm:rounded-3xl">
                                    <p class="text-sm font-medium leading-6 text-zinc-600">
                                        {{ __('Saadad pöördumise sisse logitud kasutajana:') }}
                                        <span class="font-bold text-emerald-950">{{ auth()->user()->email }}</span>
                                    </p>
                                </div>
                            @endguest

                            <div>
                                <label for="support_subject" class="mb-2 block text-sm font-bold text-emerald-950 sm:mb-3">
                                    {{ __('Pealkiri') }}
                                    <span class="font-medium text-zinc-500">({{ __('valikuline') }})</span>
                                </label>

                                <input
                                    id="support_subject"
                                    type="text"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    maxlength="150"
                                    class="w-full rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 sm:rounded-3xl"
                                    placeholder="{{ __('Näiteks: probleem kuulutuse lisamisel') }}"
                                >

                                @error('subject')
                                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="support_message" class="mb-2 block text-sm font-bold text-emerald-950 sm:mb-3">
                                    {{ __('Sõnum') }}
                                    <span class="text-red-500">*</span>
                                </label>

                                <textarea
                                    id="support_message"
                                    name="message"
                                    rows="4"
                                    required
                                    minlength="10"
                                    maxlength="3000"
                                    class="w-full resize-none rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-950 placeholder:text-zinc-400 outline-none transition focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 sm:rounded-3xl"
                                    placeholder="{{ __('Kirjuta siia oma küsimus, probleem või ettepanek...') }}"
                                >{{ old('message') }}</textarea>

                                <div class="mt-2 flex items-center justify-between gap-4">
                                    @error('message')
                                        <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                                    @else
                                        <p class="text-xs font-medium leading-5 text-zinc-500">
                                            {{ __('Minimaalselt 10 tähemärki.') }}
                                        </p>
                                    @enderror

                                    <span class="shrink-0 text-xs font-bold text-zinc-400">
                                        {{ __('Max 3000') }}
                                    </span>
                                </div>
                            </div>

                            <input
                                type="text"
                                name="website"
                                class="hidden"
                                tabindex="-1"
                                autocomplete="off"
                            >

                            @error('website')
                                <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-emerald-950/10 bg-white px-4 py-3 sm:px-7 sm:py-5">
                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
                            <button
                                type="button"
                                @click="closeModal()"
                                class="inline-flex items-center justify-center rounded-2xl border border-emerald-950/10 bg-white px-5 py-3 text-sm font-bold text-emerald-950 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10"
                            >
                                {{ __('Tühista') }}
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                            >
                                {{ __('Saada pöördumine') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>