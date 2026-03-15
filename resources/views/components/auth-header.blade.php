@props([
    'title',
    'description' => null,
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
        {{ $title }}
    </h1>

    @if ($description)
        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
            {{ $description }}
        </p>
    @endif
</div>