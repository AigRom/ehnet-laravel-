<button
    type="button"
    wire:click.stop.prevent="toggle"
    class="flex items-center justify-center rounded-full bg-white/90 p-2 shadow backdrop-blur transition hover:scale-110"
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="{{ $isFavorited ? 'currentColor' : 'none' }}"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="h-5 w-5 {{ $isFavorited ? 'text-rose-500' : 'text-zinc-500' }}"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M21.752 7.25c0-2.485-2.015-4.5-4.5-4.5-1.74 0-3.255.996-4.002 2.449C12.503 3.746 10.988 2.75 9.248 2.75c-2.485 0-4.5 2.015-4.5 4.5 0 6.75 9 12 9 12s9-5.25 9-12z"
        />
    </svg>
</button>