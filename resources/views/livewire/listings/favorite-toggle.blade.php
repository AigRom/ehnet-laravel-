<button
    type="button"
    wire:click.stop.prevent="toggle"
    class="flex items-center justify-center rounded-full bg-white/90 p-2 shadow backdrop-blur transition hover:scale-110"
    aria-label="{{ $isFavorited ? __('Eemalda lemmikutest') : __('Lisa lemmikutesse') }}"
>
    <x-icons.heart
        :filled="$isFavorited"
        class="h-5 w-5 {{ $isFavorited ? 'text-rose-500' : 'text-zinc-500' }}"
    />
</button>