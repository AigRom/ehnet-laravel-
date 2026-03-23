@props(['class' => 'w-5 h-5'])

<svg
    {{ $attributes->merge(['class' => $class]) }}
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="3"
>
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 6.75h.008v.008H12V6.75Zm0 5.25h.008v.008H12V12Zm0 5.25h.008v.008H12v-.008Z" />
</svg>