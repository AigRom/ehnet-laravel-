@props([
    'seller',
    'listing',
    'profileUrl' => null,
])

@php
    $joinedYear = optional($seller->created_at)?->format('Y');
    $roleLabel = $seller->company_name ? __('Ettevõte') : __('Eraisik');
@endphp

<div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-zinc-900">
            {{ __('Müüja') }}
        </h2>
    </div>

    @if($profileUrl)
        <a
            href="{{ $profileUrl }}"
            class="block rounded-2xl transition hover:bg-zinc-50"
        >
            <x-users.profile-card
                :user="$seller"
                :role-label="$roleLabel"
                :joined-year="$joinedYear"
                :location-label="null"
                class="border-0 bg-transparent p-0 shadow-none"
            />
        </a>
    @else
        <x-users.profile-card
            :user="$seller"
            :role-label="$roleLabel"
            :joined-year="$joinedYear"
            :location-label="null"
            class="border-0 bg-transparent p-0 shadow-none"
        />
    @endif
</div>