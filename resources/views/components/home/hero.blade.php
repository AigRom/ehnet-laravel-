@props([
    'categories',
    'usersCount' => 12458,
    'listingsCount' => 4231,
    'savedCo2' => '2 341 t',
])

<section class="relative overflow-hidden bg-gradient-to-br from-white via-emerald-50/40 to-lime-50/70">
    <div class="pointer-events-none absolute -right-24 top-6 h-80 w-80 rounded-full bg-emerald-100 blur-3xl"></div>
    <div class="pointer-events-none absolute right-24 top-12 hidden h-64 w-64 rounded-full bg-lime-100 blur-3xl lg:block"></div>

    <div class="relative mx-auto max-w-[1500px] px-4 pt-3 pb-3 sm:px-6 sm:pt-4 lg:px-8 lg:pt-4 lg:pb-2">

        {{-- Hero main row --}}
        <div class="grid items-center gap-4 lg:grid-cols-[0.95fr_1.05fr] lg:gap-8">

            {{-- Left text --}}
            <div class="relative z-10">
                <h1 class="max-w-2xl text-4xl font-extrabold leading-tight tracking-tight text-emerald-950 sm:text-5xl lg:text-6xl">
                    Ehitusmaterjalidele
                    <span class="block text-emerald-700">uus elu</span>
                </h1>

                <p class="mt-3 max-w-xl text-base leading-7 text-zinc-700 sm:text-lg">
                    Osta, müü või anneta ehitusmaterjale.
                    Aita vähendada jäätmeid ja säästa keskkonda.
                </p>
            </div>

            {{-- Right visual --}}
            <div class="relative z-10 -mt-2 flex justify-center lg:-mt-30 lg:justify-end">
                <img
                    src="/images/hero/ehnet-hero.png"
                    alt="Ehitusmaterjalid"
                    class="w-full max-w-[660px] object-contain drop-shadow-2xl"
                >
            </div>
        </div>

        {{-- Stats row --}}
        <div class="relative z-10 mt-1 grid gap-4 sm:grid-cols-3 lg:-mt-2 lg:gap-8">

            {{-- Users --}}
            <div class="flex items-center gap-4">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.stats-users class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <div class="text-xl font-extrabold leading-tight text-emerald-950">
                        {{ number_format($usersCount, 0, ',', ' ') }}
                    </div>
                    <div class="text-sm font-medium text-zinc-500">
                        {{ __('kasutajat') }}
                    </div>
                </div>
            </div>

            {{-- Listings --}}
            <div class="flex items-center gap-4">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.stats-listings class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <div class="text-xl font-extrabold leading-tight text-emerald-950">
                        {{ number_format($listingsCount, 0, ',', ' ') }}
                    </div>
                    <div class="text-sm font-medium text-zinc-500">
                        {{ __('kuulutust') }}
                    </div>
                </div>
            </div>

            {{-- CO2 --}}
            <div class="flex items-center gap-4">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.stats-recycle class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <div class="text-xl font-extrabold leading-tight text-emerald-950">
                        {{ $savedCo2 }}
                    </div>
                    <div class="text-sm font-medium text-zinc-500">
                        {{ __('CO₂ säästetud') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search bar --}}
    <div class="relative z-20 mx-auto max-w-[1500px] px-4 pb-4 sm:px-6 lg:px-8 lg:pb-5">
        <x-listings.search-bar :categories="$categories" />
    </div>
</section>