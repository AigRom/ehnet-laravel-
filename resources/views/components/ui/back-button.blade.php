@props([
    'href' => null,
    'fallback' => null,
    'label' => null,
    'color' => 'emerald',
    'size' => 'md',
])

@php
    $targetUrl = $href ?: ($fallback ?: route('dashboard'));
    $labelText = $label ?: __('Tagasi');

    $sizeClasses = match($size) {
        'sm' => 'h-9 w-9',
        'lg' => 'h-12 w-12',
        default => 'h-10 w-10',
    };

    $iconSizeClasses = match($size) {
        'sm' => 'h-4 w-4',
        'lg' => 'h-6 w-6',
        default => 'h-5 w-5',
    };

    $colorClasses = match($color) {
        'emerald' => 'bg-emerald-900 hover:bg-emerald-800 shadow-emerald-950/20 focus:ring-emerald-900/20',
        'light' => 'border border-emerald-950/10 bg-white text-emerald-950 hover:bg-emerald-50 hover:text-emerald-800 shadow-emerald-950/5 focus:ring-emerald-900/10',
        'zinc' => 'bg-zinc-800 hover:bg-zinc-700 shadow-zinc-950/20 focus:ring-zinc-800/20',
        default => 'bg-emerald-900 hover:bg-emerald-800 shadow-emerald-950/20 focus:ring-emerald-900/20',
    };

    $textColor = $color === 'light' ? '' : 'text-white';
@endphp

<a
    href="{{ $targetUrl }}"
    title="{{ $labelText }}"
    aria-label="{{ $labelText }}"
    wire:navigate
    {{ $attributes->merge([
        'class' => "inline-flex $sizeClasses items-center justify-center rounded-2xl $textColor shadow-lg transition hover:shadow-xl focus:outline-none focus:ring-4 $colorClasses",
    ]) }}
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="2"
        stroke="currentColor"
        class="{{ $iconSizeClasses }}"
        aria-hidden="true"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15.75 19.5L8.25 12l7.5-7.5"
        />
    </svg>
</a>