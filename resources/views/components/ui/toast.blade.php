@props([
    // Session võtmed, mida toast kuulab
    'successKey' => 'success',
    'errorKey' => 'error',

    // Kui kaua toast nähtav on (ms)
    'duration' => 4000,
])

@php
    $successMessage = session($successKey) ?: session('status');
    $errorMessage = session($errorKey);

    if (!$successMessage && !$errorMessage && $errors->any()) {
        $errorMessage = __('Palun paranda vormi vead.');
    }

    $message = $successMessage ?: $errorMessage;
    $type = $successMessage ? 'success' : ($errorMessage ? 'error' : null);

    $title = match ($type) {
        'success' => 'Õnnestus',
        'error' => 'Kontrolli vormi',
        default => null,
    };
@endphp

@if($message && $type)
    <div
        x-data="{
            open: true,
            duration: {{ (int) $duration }},
            progress: 100,
            init() {
                const start = Date.now();
                const timer = setInterval(() => {
                    const elapsed = Date.now() - start;
                    this.progress = Math.max(0, 100 - (elapsed / this.duration) * 100);

                    if (elapsed >= this.duration) {
                        this.open = false;
                        clearInterval(timer);
                    }
                }, 30);
            }
        }"
        x-init="init()"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-3 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
        class="pointer-events-none fixed inset-x-0 top-20 z-[100] flex justify-center px-4 sm:px-6"
    >
        <div
            @class([
                'pointer-events-auto relative w-full max-w-lg overflow-hidden rounded-[22px] border bg-white/95 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.25)] backdrop-blur-xl ring-1',
                'border-emerald-100 ring-emerald-100/70' => $type === 'success',
                'border-red-100 ring-red-100/70' => $type === 'error',
            ])
        >
            {{-- Ülemine värviriba --}}
            <div
                @class([
                    'h-1.5 w-full',
                    'bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600' => $type === 'success',
                    'bg-gradient-to-r from-red-400 via-red-500 to-rose-600' => $type === 'error',
                ])
            ></div>

            <div class="p-4 sm:p-5">
                <div class="flex items-start gap-4">
                    {{-- Ikoon --}}
                    <div
                        @class([
                            'flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-sm ring-1',
                            'bg-emerald-50 text-emerald-600 ring-emerald-100' => $type === 'success',
                            'bg-red-50 text-red-600 ring-red-100' => $type === 'error',
                        ])
                    >
                        @if($type === 'success')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.42-.004l-4-4a1 1 0 1 1 1.414-1.414l3.294 3.293 7.296-7.289a1 1 0 0 1 1.41 0Z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 8a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 14Z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>

                    {{-- Sisu --}}
                    <div class="min-w-0 flex-1">
                        <p
                            @class([
                                'text-sm font-semibold tracking-tight',
                                'text-emerald-900' => $type === 'success',
                                'text-red-900' => $type === 'error',
                            ])
                        >
                            {{ $title }}
                        </p>

                        <p class="mt-1 text-sm leading-5 text-zinc-600">
                            {{ $message }}
                        </p>
                    </div>

                    {{-- Sulgemine --}}
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-full p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700"
                        title="{{ __('Sulge') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="h-1 w-full bg-zinc-100">
                <div
                    class="h-1 transition-[width] duration-75 ease-linear"
                    :style="`width: ${progress}%`"
                    @class([
                        'bg-emerald-500' => $type === 'success',
                        'bg-red-500' => $type === 'error',
                    ])
                ></div>
            </div>
        </div>
    </div>
@endif