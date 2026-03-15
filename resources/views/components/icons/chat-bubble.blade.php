@props(['class' => 'w-5 h-5'])

<svg
    {{ $attributes->merge(['class' => $class]) }}
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="1.5"
>
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.965 2.084-2.1 2.084H14.1a.75.75 0 0 0-.53.22l-3.11 3.11a.75.75 0 0 1-1.28-.53v-2.8a.75.75 0 0 0-.75-.75H4.35c-1.135 0-2.1-.948-2.1-2.084V10.608c0-.97.616-1.813 1.5-2.097m16.5 0A2.25 2.25 0 0 0 18 6.75H6A2.25 2.25 0 0 0 3.75 8.511m16.5 0v-.001Z" />
</svg>