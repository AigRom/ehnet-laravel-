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
          d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l9.107-9.107a3 3 0 114.243 4.243l-9.9 9.9a1.5 1.5 0 01-2.121-2.122l8.839-8.838" />
</svg>