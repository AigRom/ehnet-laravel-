@props([
    'user',
    'title' => null,
])

@php
    $cardTitle = $title ?? __('Kontaktandmed');

    $isBusiness = filled($user->company_name);

    $privateFullName = trim(collect([
        $user->first_name,
        $user->last_name,
    ])->filter()->implode(' '));

    if ($privateFullName === '') {
        $privateFullName = $user->name ?? __('Lisamata');
    }

    $contactFullName = trim(collect([
        $user->contact_first_name,
        $user->contact_last_name,
    ])->filter()->implode(' '));

    $email = $user->email;

    $phone = trim((string) $user->phone);

    $phoneDisplay = $phone !== ''
        ? (str_starts_with($phone, '+') ? $phone : '+' . $phone)
        : null;

    $phoneHref = $phoneDisplay
        ? preg_replace('/\s+/', '', $phoneDisplay)
        : null;
@endphp

<div
    x-data="{ open: window.innerWidth >= 1024 }"
    class="rounded-lg border border-zinc-200 bg-white px-2.5 py-2 shadow-sm"
>
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between gap-3 text-left"
        :aria-expanded="open.toString()"
    >
        <h3 class="text-[11px] font-semibold uppercase tracking-wide text-zinc-600">
            {{ $cardTitle }}
        </h3>

        <span
            class="inline-flex h-6 w-6 items-center justify-center rounded-full text-zinc-500 transition"
            :class="{ 'rotate-180': open }"
        >
            <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-2"
    >
        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs sm:text-sm lg:grid-cols-3">
            @if($isBusiness)
                <div class="min-w-0">
                    <dt class="text-[10px] uppercase tracking-wide text-zinc-500">
                        {{ __('Ettevõte') }}
                    </dt>
                    <dd class="truncate font-medium text-zinc-900">
                        {{ $user->company_name }}
                    </dd>
                </div>

                @if($user->company_reg_no)
                    <div class="min-w-0">
                        <dt class="text-[10px] uppercase tracking-wide text-zinc-500">
                            {{ __('Reg kood') }}
                        </dt>
                        <dd class="truncate text-zinc-900">
                            {{ $user->company_reg_no }}
                        </dd>
                    </div>
                @endif

                @if($contactFullName !== '')
                    <div class="min-w-0">
                        <dt class="text-[10px] uppercase tracking-wide text-zinc-500">
                            {{ __('Kontakt') }}
                        </dt>
                        <dd class="truncate font-medium text-zinc-900">
                            {{ $contactFullName }}
                        </dd>
                    </div>
                @endif
            @else
                <div class="min-w-0">
                    <dt class="text-[10px] uppercase tracking-wide text-zinc-500">
                        {{ __('Nimi') }}
                    </dt>
                    <dd class="truncate font-medium text-zinc-900">
                        {{ $privateFullName }}
                    </dd>
                </div>
            @endif

            @if($phoneDisplay)
                <div class="min-w-0">
                    <dt class="text-[10px] uppercase tracking-wide text-zinc-500">
                        {{ __('Tel') }}
                    </dt>
                    <dd>
                        <a
                            href="tel:{{ $phoneHref }}"
                            class="block truncate font-medium text-emerald-700 hover:text-emerald-800"
                        >
                            {{ $phoneDisplay }}
                        </a>
                    </dd>
                </div>
            @endif

            @if($email)
                <div class="min-w-0">
                    <dt class="text-[10px] uppercase tracking-wide text-zinc-500">
                        {{ __('E-post') }}
                    </dt>
                    <dd>
                        <a
                            href="mailto:{{ $email }}"
                            class="block truncate font-medium text-emerald-700 hover:text-emerald-800"
                        >
                            {{ $email }}
                        </a>
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</div>