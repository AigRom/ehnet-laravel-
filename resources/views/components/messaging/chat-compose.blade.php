@props([
    'conversation',
])

<div
    x-data="messageCompose()"
    class="border-t border-emerald-200 bg-white p-4 md:p-5"
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
            id="attachments-{{ $conversation->id }}"
            multiple
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
            class="hidden"
            @change="onFilesSelected($event)"
        />

        <div class="flex items-end gap-3 w-full">
            <button
                type="button"
                onclick="document.getElementById('attachments-{{ $conversation->id }}').click()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow hover:bg-emerald-700 transition"
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
                id="body"
                name="body"
                rows="1"
                placeholder="{{ __('Kirjuta vastus...') }}"
                class="flex-1 w-full min-w-0 resize-none overflow-hidden rounded-2xl border border-emerald-400 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
            >{{ old('body') }}</textarea>

            <button
                x-show="canSend()"
                x-transition.opacity.scale.duration.150ms
                type="submit"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow hover:bg-emerald-700 transition"
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
                                        class="h-20 w-20 shrink-0 rounded-lg object-cover border border-zinc-200"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-medium text-sm text-zinc-900" x-text="item.name"></div>
                                        <div class="mt-1 text-xs text-zinc-500" x-text="formatSize(item.size)"></div>

                                        <button
                                            type="button"
                                            class="mt-2 text-xs font-medium text-red-600 hover:underline"
                                            @click="removeFile(index, 'attachments-{{ $conversation->id }}')"
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
                                        class="text-xs font-medium text-red-600 hover:underline"
                                        @click="removeFile(index, 'attachments-{{ $conversation->id }}')"
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