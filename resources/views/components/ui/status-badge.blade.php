@props([
    'status' => null,
    'expired' => false,
])

@php
    $base = 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium';

    $color = match ($status) {
        // Listing staatused
        'reserved' => 'border-amber-200 bg-amber-100 text-amber-800',
        'sold' => 'border-emerald-200 bg-emerald-100 text-emerald-800',
        'archived' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
        'draft' => 'border-sky-200 bg-sky-100 text-sky-800',
        'pending' => 'border-violet-200 bg-violet-100 text-violet-800',
        'rejected' => 'border-rose-200 bg-rose-100 text-rose-800',
        'deleted' => 'border-red-200 bg-red-100 text-red-800',

        // Trade staatused
        'interest' => 'border-blue-200 bg-blue-100 text-blue-700',
        'completed' => 'border-emerald-200 bg-emerald-100 text-emerald-700',
        'cancelled' => 'border-zinc-200 bg-zinc-100 text-zinc-700',
        'received' => 'border-blue-200 bg-blue-100 text-blue-700',

        default => $expired
            ? 'border-orange-200 bg-orange-100 text-orange-800'
            : 'border-emerald-200 bg-emerald-100 text-emerald-800',
    };
@endphp

<span {{ $attributes->merge(['class' => $base . ' ' . $color]) }}>
    {{ $slot }}
</span>