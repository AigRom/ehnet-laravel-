
@props([
    'href' => url()->previous(),
    'color' => 'emerald',
    'size' => 'md'
])

@php
$sizeClasses = match($size) {
    'sm' => 'h-8 w-8',
    'lg' => 'h-12 w-12',
    default => 'h-10 w-10'
};

$colorClasses = match($color) {
    'emerald' => 'bg-emerald-600 hover:bg-emerald-700',
    'blue' => 'bg-blue-600 hover:bg-blue-700',
    'zinc' => 'bg-zinc-600 hover:bg-zinc-700',
    default => 'bg-emerald-600 hover:bg-emerald-700'
};
@endphp

<a
    href="{{ $href }}"
    title="{{ __('Tagasi') }}"
    {{ $attributes->merge([
        'class' => "flex $sizeClasses items-center justify-center rounded-full text-white shadow-sm transition $colorClasses"
    ]) }}
    aria-label="{{ __('Tagasi') }}"
>
    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke-width="2"
         stroke="currentColor"
         class="h-5 w-5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.75 19.5L8.25 12l7.5-7.5" />
    </svg>
</a>