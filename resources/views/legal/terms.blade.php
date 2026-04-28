<x-layouts.app.public :title="__('Kasutustingimused')">
    <div class="mx-auto max-w-3xl py-10">
        <div class="mb-6">
            <x-ui.back-button />
        </div>

        <div class="rounded-[2rem] border border-emerald-950/10 bg-white p-6 shadow-xl shadow-emerald-950/5 sm:p-8">
            <h1 class="text-2xl font-extrabold tracking-tight text-emerald-950 sm:text-3xl">
                {{ __('Kasutustingimused') }}
            </h1>

            <p class="mt-6 text-base leading-7 text-zinc-600 dark:text-zinc-300">
                {{ __('Siia tulevad EHNET kasutustingimused.') }}
            </p>
        </div>
    </div>
</x-layouts.app.public>