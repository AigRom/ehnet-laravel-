@props(['class' => 'w-5 h-5'])

<svg {{ $attributes->merge(['class' => $class]) }}
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke-width="1.8"
     stroke="currentColor"
     aria-hidden="true">
    <path stroke-linecap="round"
          stroke-linejoin="round"
          d="M6 12 3 3l18 9-18 9 3-9Zm0 0h7.5" />
</svg>