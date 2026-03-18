@props([
    'conversation',
])

<div
    x-data="messageCompose()"
    class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 md:p-5"
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
                x-init="autosizeTextarea($el)"
                @input="autosizeTextarea($el)"
                id="body"
                name="body"
                rows="1"
                placeholder="{{ __('Kirjuta vastus...') }}"
                class="flex-1 w-full min-w-0 resize-none overflow-y-auto rounded-2xl border border-emerald-400 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
            >{{ old('body') }}</textarea>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700"
            >
                {{ __('Saada') }}
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
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/40">
                <div class="mb-3 text-xs font-medium uppercase tracking-wide text-zinc-500">
                    {{ __('Valitud failid') }}
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in files" :key="item.id">
                        <div class="rounded-xl bg-white p-2 shadow-sm dark:bg-zinc-900">
                            <template x-if="item.isImage">
                                <div class="flex items-start gap-3">
                                    <img
                                        :src="item.previewUrl"
                                        :alt="item.name"
                                        class="h-20 w-20 shrink-0 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-medium text-sm text-zinc-900 dark:text-zinc-100" x-text="item.name"></div>
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
                                        <div class="truncate font-medium text-zinc-900 dark:text-zinc-100" x-text="item.name"></div>
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