<x-layouts.app>
    <section class="py-10 sm:py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm shadow-emerald-950/5 sm:p-8">
                <div class="mb-4 inline-flex items-center rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-bold text-emerald-900">
                    EHNET privaatsus
                </div>

                <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 sm:text-4xl">
                    Privaatsustingimused
                </h1>

                <p class="mt-4 text-base leading-7 text-zinc-600">
                    EHNET on hetkel arendusfaasis
                </p>

                <p class="mt-4 text-sm font-medium text-zinc-500">
                    Viimati uuendatud: {{ now()->format('d.m.Y') }}
                </p>
            </div>

            <div class="mt-6 space-y-6">
                <div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 text-amber-900 sm:p-8">
                    <h2 class="text-xl font-extrabold">
                        Oluline märkus
                    </h2>

                    <p class="mt-3 leading-7">
                        EHNET ei ole hetkel avalikult kasutatav teenus. Arendusfaasis kasutatakse platvormil testandmeid,
                        näiteks testkasutajaid, näidiskuulutusi, testvestlusi ja muid arenduseks vajalikke näidisandmeid.
                    </p>

                    <p class="mt-3 leading-7">
                        Platvormile ei ole arendusfaasis ette nähtud sisestada päris kasutajate isikuandmeid,
                        päris müügikuulutusi, makseandmeid ega konfidentsiaalset infot.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Andmete kasutamine arendusfaasis
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Arendus- ja testkeskkonnas kasutatakse andmeid ainult platvormi funktsioonide kontrollimiseks
                        ja demonstreerimiseks. Sellised funktsioonid on näiteks kasutajakonto loomine, sisselogimine,
                        kuulutuste lisamine, sõnumite saatmine, ostusoovi esitamine ja broneeringute testimine.
                    </p>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Testandmeid ei kasutata reklaamiks, turunduseks ega edastata müügiks kolmandatele osapooltele.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Küpsised
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        EHNET kasutab hetkel ainult platvormi toimimiseks vajalikke tehnilisi küpsiseid, näiteks
                        sessiooni hoidmiseks, sisselogimise toimimiseks ja vormide kaitsmiseks.
                    </p>

                    <p class="mt-4 leading-7 text-zinc-700">
                        EHNET ei kasuta hetkel analüütika-, reklaami- ega turundusküpsiseid.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Lõplikud privaatsustingimused
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Enne EHNET platvormi avalikku kasutuselevõttu koostatakse ja avaldatakse täpsed
                        privaatsustingimused, kus kirjeldatakse platvormi tegelikku andmetöötlust, vastutavat
                        töötlejat, kasutaja õigusi ja andmete säilitamise põhimõtteid.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>