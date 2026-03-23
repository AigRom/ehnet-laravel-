@props([
    'icon' => null,
    'danger' => false,
])

<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' => '
            flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition
            ' . ($danger
                ? 'text-zinc-700 hover:bg-red-50 hover:text-red-700'
                : 'text-zinc-700 hover:bg-zinc-100')
    ]) }}
>
    @if($icon)
        <x-dynamic-component
            :component="$icon"
            class="h-4 w-4 shrink-0 {{ $danger ? 'text-red-500' : 'text-zinc-400' }}"
        />
    @endif

    <span>
        {{ $slot }}
    </span>
</button>