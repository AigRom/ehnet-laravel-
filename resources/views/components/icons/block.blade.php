@props(['class' => 'w-5 h-5'])

<svg {{ $attributes->merge(['class' => $class]) }}
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke="currentColor"
     stroke-width="1.5">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M18 12A6 6 0 1 1 6 12a6 6 0 0 1 12 0Zm-9.75 0h7.5" />
</svg>