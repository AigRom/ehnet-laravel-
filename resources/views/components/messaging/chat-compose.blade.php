@props([
    'conversation',
    'hasMessagingBlock' => false,
    'isBlockedByMe' => false,
    'unblockUserAction' => null,
])

@php
    $fileInputId = 'attachments-' . $conversation->id;

    $attachButtonClasses = 'inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-emerald-950/10 bg-white text-emerald-900 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-900/10';

    $sendButtonClasses = 'inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-900 text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20';

    $removeButtonClasses = 'text-xs font-bold text-red-600 transition hover:text-red-700 hover:underline';
@endphp

<div class="shrink-0 border-t border-emerald-950/10 bg-white/95 p-3 backdrop-blur sm:p-4 md:p-5">
    @if($hasMessagingBlock)
        <div class="rounded-[1.5rem] border border-zinc-200 bg-stone-50 p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-600">
                    <x-icons.block class="h-5 w-5" />
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-zinc-900">
                        {{ __('Sõnumite saatmine on piiratud') }}
                    </h3>

                    @if($isBlockedByMe)
                        <p class="mt-1 text-sm leading-6 text-zinc-600">
                            {{ __('Oled selle kasutaja blokeerinud. Blokeeringu eemaldamisel saate jälle uusi sõnumeid vahetada.') }}
                        </p>

                        @if($unblockUserAction)
                            <form method="POST" action="{{ $unblockUserAction }}" class="mt-4">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-4 focus:ring-zinc-900/15"
                                >
                                    {{ __('Eemalda blokeering') }}
                                </button>
                            </form>
                        @endif
                    @else
                        <p class="mt-1 text-sm leading-6 text-zinc-600">
                            {{ __('Selle kasutajaga ei saa praegu uusi sõnumeid vahetada.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div
            x-data="messageCompose()"
            x-init="bodyText = @js(old('body', ''))"
        >
            <form
                method="POST"
                action="{{ route('messages.store', $conversation) }}"
                enctype="multipart/form-data"
                class="space-y-3"
            >
                @csrf

                <input
                    type="file"
                    name="attachments[]"
                    id="{{ $fileInputId }}"
                    multiple
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                    class="hidden"
                    @change="onFilesSelected($event)"
                />

                <div class="flex w-full items-end gap-2 sm:gap-3">
                    <button
                        type="button"
                        onclick="document.getElementById('{{ $fileInputId }}').click()"
                        class="{{ $attachButtonClasses }}"
                        title="{{ __('Lisa fail') }}"
                        aria-label="{{ __('Lisa fail') }}"
                    >
                        <x-icons.paperclip class="h-5 w-5" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <textarea
                            x-ref="body"
                            x-model="bodyText"
                            x-init="autosizeTextarea($el)"
                            @input="autosizeTextarea($el)"
                            @keydown="
                                if (event.key === 'Enter' && !event.shiftKey) {
                                    event.preventDefault();

                                    if (canSend()) {
                                        $el.form.requestSubmit();
                                    }
                                }
                            "
                            name="body"
                            rows="1"
                            placeholder="{{ __('Kirjuta vastus...') }}"
                            class="block max-h-36 min-h-[44px] w-full resize-none overflow-hidden rounded-2xl border border-emerald-950/10 bg-stone-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-950 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-emerald-900/30 focus:bg-white focus:ring-4 focus:ring-emerald-900/10 sm:px-5 sm:text-base"
                        >{{ old('body') }}</textarea>
                    </div>

                    <button
                        x-show="canSend()"
                        x-transition.opacity.scale.duration.150ms
                        type="submit"
                        class="{{ $sendButtonClasses }}"
                        title="{{ __('Saada') }}"
                        aria-label="{{ __('Saada') }}"
                    >
                        <x-icons.paper-airplane class="h-5 w-5" />
                    </button>
                </div>

                @error('body')
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                @error('attachments')
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                @error('attachments.*')
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                <template x-if="files.length > 0">
                    <div class="rounded-[1.5rem] border border-emerald-950/10 bg-stone-50 p-3 shadow-sm">
                        <div class="mb-3 text-xs font-bold uppercase tracking-wide text-emerald-900/70">
                            {{ __('Valitud failid') }}
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in files" :key="item.id">
                                <div class="rounded-2xl border border-zinc-200 bg-white p-2 shadow-sm">
                                    <template x-if="item.isImage">
                                        <div class="flex items-start gap-3">
                                            <img
                                                :src="item.previewUrl"
                                                :alt="item.name"
                                                class="h-20 w-20 shrink-0 rounded-xl border border-zinc-200 object-cover"
                                            >

                                            <div class="min-w-0 flex-1 pt-1">
                                                <div class="truncate text-sm font-bold text-zinc-900" x-text="item.name"></div>
                                                <div class="mt-1 text-xs text-zinc-500" x-text="formatSize(item.size)"></div>

                                                <button
                                                    type="button"
                                                    class="{{ $removeButtonClasses }} mt-2"
                                                    @click="removeFile(index, '{{ $fileInputId }}')"
                                                >
                                                    {{ __('Eemalda') }}
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!item.isImage">
                                        <div class="flex items-center justify-between gap-3 px-2 py-2 text-sm">
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate font-bold text-zinc-900" x-text="item.name"></div>
                                                <div class="mt-0.5 text-xs text-zinc-500" x-text="formatSize(item.size)"></div>
                                            </div>

                                            <button
                                                type="button"
                                                class="{{ $removeButtonClasses }} shrink-0"
                                                @click="removeFile(index, '{{ $fileInputId }}')"
                                            >
                                                {{ __('Eemalda') }}
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </form>
        </div>
    @endif
</div>