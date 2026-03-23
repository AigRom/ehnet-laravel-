@props(['class' => 'w-5 h-5'])

<svg {{ $attributes->merge(['class' => $class]) }}
     xmlns="http://www.w3.org/2000/svg"
     fill="none"
     viewBox="0 0 24 24"
     stroke="currentColor"
     stroke-width="1.5">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM12 3c4.97 0 9 4.03 9 9s-4.03 9-9 9-9-4.03-9-9 4.03-9 9-9Z" />
</svg>