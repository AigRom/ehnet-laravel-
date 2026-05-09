<x-layouts.app.public :title="__('Minu EHNET')">
    @php
        $user = auth()->user();

        $displayName =
            $user->company_name
            ?? $user->name
            ?? $user->email
            ?? __('Kasutaja');

        $greetingName = $user->name ?? __('kasutaja');
        $memberSince = optional($user->created_at)?->format('d.m.Y');

        $locationLabel =
            $user->location?->full_label_et
            ?? $user->location?->name
            ?? __('Asukoht lisamata');

        $avatarUrl = $user->avatar_url ?? null;

        $activeListingsCount = $activeListingsCount ?? 0;
        $favoritesCount = $favoritesCount ?? 0;

        $reviewsCountValue = $user->reviewsCount();
        $scoreValue = $user->averageRating();
        $hasReviews = $user->hasReviews();

        $reviewsUrl = \Illuminate\Support\Facades\Route::has('reviews.received')
            ? route('reviews.received')
            : route('users.show', $user);
    @endphp

    <div class="mx-auto w-full max-w-[1500px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white shadow-xl shadow-emerald-950/5">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-emerald-200/50 blur-3xl"></div>
                <div class="absolute -right-24 top-1/2 h-80 w-80 rounded-full bg-lime-200/40 blur-3xl"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/90 via-white to-stone-50"></div>
            </div>

            <div class="relative grid gap-6 p-5 sm:p-6 lg:grid-cols-12 lg:p-8">
                <div class="lg:col-span-7 xl:col-span-8">
                    <div class="mb-3 inline-flex items-center rounded-full bg-emerald-900 px-3 py-1 text-xs font-extrabold text-white shadow-sm">
                        {{ __('Minu EHNET') }}
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 sm:text-4xl">
                        {{ __('Tere, :name', ['name' => $greetingName]) }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-zinc-600 sm:text-base">
                        {{ __('Halda siin oma profiili, kuulutusi, oste, sõnumeid ja lemmikuid.') }}
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a
                            href="{{ route('listings.create') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-800 hover:shadow-xl hover:shadow-emerald-950/25 focus:outline-none focus:ring-4 focus:ring-emerald-900/20"
                        >
                            <x-icons.plus-circle class="h-5 w-5" />
                            <span>{{ __('Lisa kuulutus') }}</span>
                        </a>

                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white/80 p-4 shadow-sm backdrop-blur">
                            <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-500">
                                {{ __('Aktiivsed kuulutused') }}
                            </div>
                            <div class="mt-2 text-3xl font-extrabold text-emerald-950">
                                {{ $activeListingsCount }}
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-emerald-950/10 bg-white/80 p-4 shadow-sm backdrop-blur">
                            <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-500">
                                {{ __('Lemmikud') }}
                            </div>
                            <div class="mt-2 text-3xl font-extrabold text-emerald-950">
                                {{ $favoritesCount }}
                            </div>
                        </div>

                        <a
                            href="{{ $reviewsUrl }}"
                            class="group rounded-[1.5rem] border border-emerald-950/10 bg-white/80 p-4 shadow-sm backdrop-blur transition hover:bg-emerald-50 hover:shadow-md"
                        >
                            <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-500">
                                {{ __('Tagasiside') }}
                            </div>

                            @if($hasReviews)
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 text-3xl font-extrabold text-emerald-950">
                                        <x-icons.star class="h-6 w-6 text-amber-500" />
                                        <span>{{ number_format($scoreValue, 1, ',', ' ') }}</span>
                                    </div>

                                    <div class="text-right text-xs font-bold text-zinc-500">
                                        {{ trans_choice(':count hinnang|:count hinnangut', $reviewsCountValue, ['count' => $reviewsCountValue]) }}
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 text-sm font-bold text-zinc-500">
                                    {{ __('Tagasiside puudub') }}
                                </div>
                            @endif

                            <div class="mt-3 flex items-center justify-between border-emerald-950/10 pt-3">
                                <span class="text-sm font-extrabold text-emerald-900">
                                    {{ __('Vaata') }}
                                </span>

                                <svg
                                    class="h-5 w-5 text-emerald-900 transition group-hover:translate-x-1"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5 xl:col-span-4">
                    <div class="h-full rounded-[2rem] border border-emerald-950/10 bg-white/90 p-5 shadow-lg shadow-emerald-950/5 backdrop-blur sm:p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-3xl border border-emerald-950/10 bg-stone-100 shadow-sm">
                                @if($avatarUrl)
                                    <img
                                        src="{{ $avatarUrl }}"
                                        alt="{{ $displayName }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <x-icons.user-circle class="h-10 w-10 text-zinc-400" />
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="break-words text-lg font-extrabold text-emerald-950">
                                    {{ $displayName }}
                                </div>

                                <div class="mt-1 break-all text-sm font-medium text-zinc-500">
                                    {{ $user->email }}
                                </div>

                                <div class="mt-2 inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-900 ring-1 ring-emerald-900/10">
                                    {{ $user->company_name ? __('Ettevõte') : __('Eraisik') }}
                                </div>
                            </div>
                        </div>

                        <dl class="mt-6 space-y-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <dt class="text-sm font-medium text-zinc-500">
                                    {{ __('Asukoht') }}
                                </dt>
                                <dd class="text-sm font-bold text-emerald-950 sm:text-right">
                                    {{ $locationLabel }}
                                </dd>
                            </div>

                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <dt class="text-sm font-medium text-zinc-500">
                                    {{ __('Liitunud') }}
                                </dt>
                                <dd class="text-sm font-bold text-emerald-950 sm:text-right">
                                    {{ $memberSince ?? '—' }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6">
                            <a
                                href="{{ route('profile.edit') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-emerald-950/10 bg-white px-5 py-4 text-base font-extrabold text-emerald-950 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 hover:shadow-md"
                            >
                                <x-icons.cog-6-tooth class="h-6 w-6" />
                                <span>{{ __('Seaded') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a
                href="{{ route('listings.mine') }}"
                class="group relative overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-900/20 hover:shadow-xl hover:shadow-emerald-950/10 sm:p-6"
            >
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-100 blur-2xl transition group-hover:bg-emerald-200"></div>

                <div class="relative flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10">
                        <x-icons.squares-2x2 class="h-7 w-7 text-emerald-900" />
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-lg font-extrabold text-emerald-950">
                            {{ __('Minu kuulutused') }}
                        </h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                            {{ __('Vaata, muuda ja halda oma lisatud kuulutusi.') }}
                        </p>
                    </div>
                </div>

                <div class="relative mt-6 flex items-center justify-between">
                    <span class="text-sm font-extrabold text-emerald-900">
                        {{ __('Ava') }}
                    </span>

                    <svg class="h-5 w-5 text-emerald-900 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a
                href="{{ route('purchases.index') }}"
                class="group relative overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-900/20 hover:shadow-xl hover:shadow-emerald-950/10 sm:p-6"
            >
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-100 blur-2xl transition group-hover:bg-emerald-200"></div>

                <div class="relative flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10">
                        <x-icons.shopping-bag class="h-7 w-7 text-emerald-900" />
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-lg font-extrabold text-emerald-950">
                            {{ __('Minu ostud') }}
                        </h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                            {{ __('Vaata ostusoove, broneeringuid ja lõpetatud tehinguid.') }}
                        </p>
                    </div>
                </div>

                <div class="relative mt-6 flex items-center justify-between">
                    <span class="text-sm font-extrabold text-emerald-900">
                        {{ __('Ava') }}
                    </span>

                    <svg class="h-5 w-5 text-emerald-900 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a
                href="{{ route('messages.index') }}"
                class="group relative overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-900/20 hover:shadow-xl hover:shadow-emerald-950/10 sm:p-6"
            >
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-100 blur-2xl transition group-hover:bg-emerald-200"></div>

                <div class="relative flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10">
                        <x-icons.chat-bubble class="h-7 w-7 text-emerald-900" />
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-lg font-extrabold text-emerald-950">
                            {{ __('Sõnumid') }}
                        </h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                            {{ __('Vestlused ostjate ja müüjatega ühes kohas.') }}
                        </p>
                    </div>
                </div>

                <div class="relative mt-6 flex items-center justify-between">
                    <span class="text-sm font-extrabold text-emerald-900">
                        {{ __('Ava') }}
                    </span>

                    <svg class="h-5 w-5 text-emerald-900 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a
                href="{{ route('favorites.index') }}"
                class="group relative overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-900/20 hover:shadow-xl hover:shadow-emerald-950/10 sm:p-6"
            >
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-100 blur-2xl transition group-hover:bg-emerald-200"></div>

                <div class="relative flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-900/10">
                        <x-icons.heart class="h-7 w-7 text-emerald-900" />
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-lg font-extrabold text-emerald-950">
                            {{ __('Lemmikud') }}
                        </h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-zinc-600">
                            {{ __('Sinu salvestatud kuulutused kiireks tagasitulekuks.') }}
                        </p>
                    </div>
                </div>

                <div class="relative mt-6 flex items-center justify-between">
                    <span class="text-sm font-extrabold text-emerald-900">
                        {{ __('Ava') }}
                    </span>

                    <svg class="h-5 w-5 text-emerald-900 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </section>
    </div>
</x-layouts.app.public>