@props([
    'status' => null,
    'expired' => false,
])

@php
    $classes = match (true) {
        $expired => 'border-orange-200 bg-orange-50 text-orange-800',

        $status === 'published' => 'border-emerald-900 bg-emerald-900 text-white',
        $status === 'sold' => 'border-emerald-700 bg-emerald-100 text-emerald-900',

        $status === 'reserved' => 'border-amber-200 bg-amber-50 text-amber-800',
        $status === 'draft' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
        $status === 'archived' => 'border-stone-200 bg-stone-100 text-stone-700',
        $status === 'pending' => 'border-sky-200 bg-sky-50 text-sky-800',
        $status === 'rejected' => 'border-red-200 bg-red-50 text-red-700',
        $status === 'deleted' => 'border-red-200 bg-red-100 text-red-800',

        default => 'border-emerald-900 bg-emerald-900 text-white',
    };
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold ' . $classes,
    ]) }}
>
    {{ $slot }}
</span>