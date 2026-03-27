@props([
    'user',
    'size' => 'h-14 w-14',
])
<div {{ $attributes->merge([
    'class' => "flex $size shrink-0 items-center justify-center overflow-hidden rounded-full border border-zinc-200 bg-zinc-100"
]) }}>
    @if($user && $user->avatar_path)
        <img
            src="{{ $user->avatar_url }}"
            alt="{{ $user->name ?? __('Kasutaja') }}"
            class="h-full w-full object-center"
        >
    @else
        <span class="text-sm font-semibold text-zinc-600">
            {{ $user?->initials() ?? '?' }}
        </span>
    @endif
</div>
