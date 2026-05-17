<x-layouts.app>
    <section class="py-10 sm:py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm shadow-emerald-950/5 sm:p-8">
                <div class="mb-4 inline-flex items-center rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-bold text-emerald-900">
                    EHNET tingimused
                </div>

                <h1 class="text-3xl font-extrabold tracking-tight text-emerald-950 sm:text-4xl">
                    Kasutustingimused
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
                        EHNET ei ole hetkel avalikult kasutatav äriline teenus. Arendusfaasis kasutatakse platvormi
                        testimiseks jafunktsionaalsuse kontrollimiseks.
                    </p>

                    <p class="mt-3 leading-7">
                        Platvormil olevad kasutajad, kuulutused, sõnumid, broneeringud ja tehingud on mõeldud
                        testimiseks ning funktsionaalsuse demonstreerimiseks.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Platvormi eesmärk
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        EHNET eesmärk on luua keskkond, kus kasutajad saavad tulevikus lisada, otsida, müüa,
                        osta, broneerida ja annetada ehitusmaterjale ning nende jääke.
                    </p>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Platvormi laiem eesmärk on toetada ehitusmaterjalide taaskasutust, vähendada raiskamist
                        ja muuta kasutuskõlblike materjalide leidmine lihtsamaks.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Arendus- ja testkasutus
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Praeguses etapis on kõik platvormil tehtavad tegevused testtegevused. Testkeskkonnas tehtud
                        ostusoove, broneeringuid, sõnumeid või tehingu staatuseid ei käsitleta päris ostu-müügi
                        tehingutena.
                    </p>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Arendusfaasis ei tohiks platvormile sisestada päris isikuandmeid, päris müügikuulutusi,
                        makseandmeid ega konfidentsiaalset infot.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Kasutaja vastutus testimisel
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Platvormi testimisel tuleb kasutada ainult näidisandmeid. Kasutaja vastutab selle eest,
                        et tema sisestatud testandmed ei sisaldaks tundlikku infot ega rikuks teiste isikute õigusi.
                    </p>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Platvormi ei tohi kasutada pahatahtlikult, süsteemi häirimiseks, teiste kasutajate eksitamiseks
                        ega sobimatu sisu lisamiseks.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-extrabold text-emerald-950">
                        Lõplikud kasutustingimused
                    </h2>

                    <p class="mt-4 leading-7 text-zinc-700">
                        Enne EHNET platvormi avalikku kasutuselevõttu koostatakse ja avaldatakse täpsed
                        kasutustingimused, kus kirjeldatakse platvormi lõplikku toimimisloogikat, kasutaja
                        vastutust, kuulutuste reegleid, tehingute põhimõtteid ja platvormi haldaja andmeid.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>