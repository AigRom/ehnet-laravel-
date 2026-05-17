@props([
    'successKey' => 'success',
    'errorKey' => 'error',
    'duration' => 4000,
])

@php
    $successMessage = session($successKey) ?: session('status');
    $errorMessage = session($errorKey);

    $message = $successMessage ?: $errorMessage;
    $type = $successMessage ? 'success' : ($errorMessage ? 'error' : 'info');

    $title = match ($type) {
        'success' => 'Õnnestus',
        'error' => 'Tähelepanu',
        default => null,
    };
@endphp

<div
    x-data="{
        open: @js((bool) $message),
        duration: {{ (int) $duration }},
        progress: 100,
        message: @js($message),
        type: @js($type),
        title: @js($title),
        timer: null,
        progressTimer: null,

        init() {
            if (this.open) {
                this.startTimer();
            }
        },

        showNotify(event) {
            this.message = event.detail.message || '';
            this.type = event.detail.type || 'info';
            this.title = event.detail.title ?? this.getTitle(this.type);
            this.open = true;
            this.progress = 100;

            this.startTimer();
        },

        getTitle(type) {
            if (type === 'success') return 'Õnnestus';
            if (type === 'error') return 'Tähelepanu';
            return null;
        },

        startTimer() {
            clearTimeout(this.timer);
            clearInterval(this.progressTimer);

            const start = Date.now();

            this.progressTimer = setInterval(() => {
                const elapsed = Date.now() - start;
                this.progress = Math.max(0, 100 - (elapsed / this.duration) * 100);

                if (elapsed >= this.duration) {
                    this.open = false;
                    clearInterval(this.progressTimer);
                }
            }, 30);

            this.timer = setTimeout(() => {
                this.open = false;
                clearInterval(this.progressTimer);
            }, this.duration);
        },

        close() {
            this.open = false;
            clearTimeout(this.timer);
            clearInterval(this.progressTimer);
        }
    }"
    x-init="init()"
    x-on:notify.window="showNotify($event)"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-3 scale-[0.98]"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
    x-cloak
    class="pointer-events-none fixed inset-x-0 top-20 z-[100] flex justify-center px-4 sm:px-6"
>
    <div
        class="pointer-events-auto relative w-full max-w-lg overflow-hidden rounded-[22px] border bg-white/95 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.25)] backdrop-blur-xl ring-1"
        :class="{
            'border-emerald-100 ring-emerald-100/70': type === 'success',
            'border-red-100 ring-red-100/70': type === 'error',
            'border-rose-100 ring-rose-100/70': type === 'info'
        }"
    >
        <div
            class="h-1.5 w-full"
            :class="{
                'bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600': type === 'success',
                'bg-gradient-to-r from-red-400 via-red-500 to-rose-600': type === 'error',
                'bg-gradient-to-r from-rose-400 via-rose-500 to-rose-600': type === 'info'
            }"
        ></div>

        <div class="p-4 sm:p-5">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1"
                    :class="{
                        'bg-emerald-50 text-emerald-600 ring-emerald-100': type === 'success',
                        'bg-red-50 text-red-600 ring-red-100': type === 'error',
                        'bg-rose-50 text-rose-500 ring-rose-100': type === 'info'
                    }"
                >
                    <svg
                        x-show="type === 'success'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.42-.004l-4-4a1 1 0 1 1 1.414-1.414l3.294 3.293 7.296-7.289a1 1 0 0 1 1.41 0Z" clip-rule="evenodd" />
                    </svg>

                    <svg
                        x-show="type === 'error'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                    </svg>

                    <svg
                        x-show="type === 'info'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.75 8.25c0-2.485-2.015-4.5-4.5-4.5-1.54 0-2.902.776-3.75 1.96a4.48 4.48 0 0 0-3.75-1.96c-2.485 0-4.5 2.015-4.5 4.5 0 6.75 8.25 11.25 8.25 11.25s8.25-4.5 8.25-11.25Z"
                        />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p
                        x-show="title"
                        class="text-sm font-semibold tracking-tight"
                        :class="{
                            'text-emerald-900': type === 'success',
                            'text-red-900': type === 'error',
                            'text-zinc-900': type === 'info'
                        }"
                        x-text="title"
                    ></p>

                    <p
                        class="text-base font-medium leading-8 text-zinc-700 sm:text-lg sm:leading-8"
                        :class="title ? 'mt-1' : ''"
                        x-text="message"
                    ></p>
                </div>

                <button
                    type="button"
                    @click="close()"
                    class="rounded-full p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700"
                    title="{{ __('Sulge') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="h-1 w-full bg-zinc-100">
            <div
                class="h-1 transition-[width] duration-75 ease-linear"
                :style="`width: ${progress}%`"
                :class="{
                    'bg-emerald-500': type === 'success',
                    'bg-red-500': type === 'error',
                    'bg-rose-500': type === 'info'
                }"
            ></div>
        </div>
    </div>
</div>