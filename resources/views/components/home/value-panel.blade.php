<section class="relative -mb-16 mt-16 border-t border-emerald-950/10 bg-white/75">
    <div class="mx-auto max-w-[1500px] px-4 py-12 sm:px-6 lg:px-8 lg:py-14">

        {{-- Section heading --}}
        <div class="mx-auto mb-10 max-w-3xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-emerald-950 sm:text-4xl">
                {{ __('Miks valida EHNET?') }}
            </h2>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Säästad raha --}}
            <div class="flex gap-4 rounded-3xl p-4 transition hover:bg-white/70">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.savings class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-emerald-950">
                        {{ __('Säästad raha') }}
                    </h3>

                    <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                        {{ __('Leia kasutuskõlblikke ehitusmaterjale soodsama hinnaga.') }}
                    </p>
                </div>
            </div>

            {{-- Aitad keskkonda --}}
            <div class="flex gap-4 rounded-3xl p-4 transition hover:bg-white/70">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.recycle class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-emerald-950">
                        {{ __('Aitad keskkonda') }}
                    </h3>

                    <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                        {{ __('Vähenda ehitusjäätmeid ja anna materjalidele uus elu.') }}
                    </p>
                </div>
            </div>

            {{-- Toetad kogukonda --}}
            <div class="flex gap-4 rounded-3xl p-4 transition hover:bg-white/70">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.community class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-emerald-950">
                        {{ __('Toetad kogukonda') }}
                    </h3>

                    <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                        {{ __('Annetused ja taaskasutus aitavad kohalikke inimesi ja projekte.') }}
                    </p>
                </div>
            </div>

            {{-- Lihtne ja turvaline --}}
            <div class="flex gap-4 rounded-3xl p-4 transition hover:bg-white/70">
                <div class="shrink-0 text-emerald-900">
                    <x-icons.shield-check class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-emerald-950">
                        {{ __('Lihtne ja turvaline') }}
                    </h3>

                    <p class="mt-1.5 text-sm leading-6 text-zinc-600">
                        {{ __('Selge kuulutuste süsteem, kasutajakontod ja turvaline suhtlus.') }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>