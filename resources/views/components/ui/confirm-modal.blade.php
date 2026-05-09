@props([
    'open' => 'false',
    'title',
    'description' => null,
    'icon' => null,
    'iconWrapperClass' => 'bg-zinc-100',
    'iconClass' => 'text-zinc-600',
    'cancelText' => __('Tühista'),
    'confirmText' => __('Kinnita'),
    'confirmButtonClass' => 'bg-zinc-900 text-white hover:bg-zinc-800',
    'confirmClick' => null,
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
        <div class="flex items-start gap-3">
            @if($icon)
                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconWrapperClass }}">
                    <x-dynamic-component :component="$icon" class="h-5 w-5 {{ $iconClass }}" />
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-zinc-900">
                    {{ $title }}
                </h3>

                @if($description)
                    <p class="mt-2 text-sm leading-6 text-zinc-600">
                        {{ $description }}
                    </p>
                @endif

                @if(trim((string) $slot))
                    <div class="mt-4">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button
                type="button"
                @click="{{ $open }} = false"
                class="inline-flex items-center rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
            >
                {{ $cancelText }}
            </button>

            <button
                type="button"
                @if($confirmClick) @click="{{ $confirmClick }}" @endif
                class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-medium transition {{ $confirmButtonClass }}"
            >
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>