@props([
    'href' => '#',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => collect([
            'inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4',
            $active
                ? 'bg-emerald-900 text-white shadow-sm shadow-emerald-950/20 ring-1 ring-emerald-900 hover:bg-emerald-800 focus:ring-emerald-900/20'
                : 'border border-emerald-950/10 bg-white text-emerald-950 hover:bg-emerald-50 hover:text-emerald-800 focus:ring-emerald-900/10',
        ])->implode(' '),
    ]) }}
>
    {{ $slot }}
</a>