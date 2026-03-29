@props([
    'conversation',
    'hasMessagingBlock' => false,
    'isBlockedByMe' => false,
    'unblockUserAction' => null,
])

@php
    $fileInputId = 'attachments-' . $conversation->id;

    $iconButtonClasses = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow transition hover:bg-emerald-700';
    $removeButtonClasses = 'text-xs font-medium text-red-600 hover:underline';
@endphp

<div class="border-t border-emerald-200 bg-white p-4 md:p-5">
    @if($hasMessagingBlock)
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-600">
                    <x-icons.block class="h-5 w-5" />
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-zinc-900">
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
                                    class="inline-flex items-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800"
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

                <div class="flex w-full items-end gap-3">
                    <button
                        type="button"
                        onclick="document.getElementById('{{ $fileInputId }}').click()"
                        class="{{ $iconButtonClasses }}"
                        title="{{ __('Lisa fail') }}"
                    >
                        <x-icons.paperclip class="h-5 w-5" />
                    </button>

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
                        class="w-full min-w-0 flex-1 resize-none overflow-hidden rounded-2xl border border-emerald-400 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                    >{{ old('body') }}</textarea>

                    <button
                        x-show="canSend()"
                        x-transition.opacity.scale.duration.150ms
                        type="submit"
                        class="{{ $iconButtonClasses }}"
                        title="{{ __('Saada') }}"
                    >
                        <x-icons.paper-airplane class="h-5 w-5" />
                    </button>
                </div>

                @error('body')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                @error('attachments')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                @error('attachments.*')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <template x-if="files.length > 0">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                        <div class="mb-3 text-xs font-medium uppercase tracking-wide text-zinc-500">
                            {{ __('Valitud failid') }}
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in files" :key="item.id">
                                <div class="rounded-xl bg-white p-2 shadow-sm">
                                    <template x-if="item.isImage">
                                        <div class="flex items-start gap-3">
                                            <img
                                                :src="item.previewUrl"
                                                :alt="item.name"
                                                class="h-20 w-20 shrink-0 rounded-lg border border-zinc-200 object-cover"
                                            >

                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-sm font-medium text-zinc-900" x-text="item.name"></div>
                                                <div class="mt-1 text-xs text-zinc-500" x-text="formatSize(item.size)"></div>

                                                <button
                                                    type="button"
                                                    class="{{ $removeButtonClasses }}"
                                                    @click="removeFile(index, '{{ $fileInputId }}')"
                                                >
                                                    {{ __('Eemalda') }}
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!item.isImage">
                                        <div class="flex items-center justify-between gap-3 px-1 py-1 text-sm">
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate font-medium text-zinc-900" x-text="item.name"></div>
                                                <div class="text-xs text-zinc-500" x-text="formatSize(item.size)"></div>
                                            </div>

                                            <button
                                                type="button"
                                                class="{{ $removeButtonClasses }}"
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