<footer class="mt-16 bg-gradient-to-br from-emerald-950 via-emerald-900 to-zinc-950 text-white">
    <div class="mx-auto max-w-[1500px] px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-10 md:grid-cols-4">

            {{-- Brand --}}
            <div class="md:col-span-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 lg:gap-4">
                    <img
                        src="{{ asset('branding/logo/ehnet-white.png') }}"
                        alt="EHNET"
                        class="h-11 w-auto lg:h-14"
                    >

                    <span class="text-xl font-extrabold tracking-tight text-white lg:text-2xl">
                        EHNET
                    </span>
                </a>

                <p class="mt-4 max-w-md text-sm leading-6 text-emerald-100/80 lg:max-w-lg lg:text-base lg:leading-7">
                    EHNET on ringmajanduslik ostu-müügi platvorm ehitusmaterjalidele.
                    Aitame kasutuskõlblikel materjalidel jõuda uue omanikuni ja vähendada raiskamist.
                </p>
            </div>

            {{-- Navigation --}}
            <div>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('listings.index') }}"
                       class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                        {{ __('Kõik kuulutused') }}
                    </a>

                    <a href="{{ auth()->check() ? route('listings.create') : route('login', ['redirect' => route('listings.create'), 'notice' => 'create_listing']) }}"
                       class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                        {{ __('Lisa kuulutus') }}
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                            {{ __('Minu EHNET') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                            {{ __('Logi sisse') }}
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Legal / Contact --}}
            <div>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('terms') }}"
                       class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                        {{ __('Kasutustingimused') }}
                    </a>

                    <a href="{{ route('privacy') }}"
                       class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                        {{ __('Privaatsus') }}
                    </a>

                    <a href="mailto:info@ehmera.ee"
                       class="block text-sm font-medium text-emerald-100/75 transition hover:text-white lg:text-base">
                        info@ehmera.ee
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-emerald-100/65 sm:flex-row sm:items-center sm:justify-between lg:text-base">
            <div>
                © {{ date('Y') }} EHNET. Kõik õigused kaitstud.
            </div>

            <div>
                Ehitusmaterjalid uuele ringile.
            </div>
        </div>
    </div>
</footer>