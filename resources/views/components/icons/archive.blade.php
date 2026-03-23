@props(['class' => 'w-5 h-5'])

<svg {{ $attributes->merge(['class' => $class]) }}
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke="currentColor"
     stroke-width="1.5">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M20.25 7.5v10.125a2.625 2.625 0 0 1-2.625 2.625H6.375A2.625 2.625 0 0 1 3.75 17.625V7.5m16.5 0-1.5-3h-12l-1.5 3m16.5 0H3.75m6 4.5h4.5" />
</svg>