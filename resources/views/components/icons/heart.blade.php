@props(['class' => 'w-5 h-5'])

<svg {{ $attributes->merge(['class' => $class]) }}
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke="currentColor"
     stroke-width="1.5">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M21.75 8.25c0-2.485-2.015-4.5-4.5-4.5-1.54 0-2.902.776-3.75 1.96a4.48 4.48 0 0 0-3.75-1.96c-2.485 0-4.5 2.015-4.5 4.5 0 6.75 8.25 11.25 8.25 11.25s8.25-4.5 8.25-11.25Z"/>
</svg>