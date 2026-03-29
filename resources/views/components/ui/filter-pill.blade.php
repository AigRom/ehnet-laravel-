@props([
    'href' => '#',
    'active' => false,
])

@php
    $classes = $active
        ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
        : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => "inline-flex items-center rounded-full border px-3 py-2 text-sm font-medium transition {$classes}",
    ]) }}
>
    {{ $slot }}
</a>