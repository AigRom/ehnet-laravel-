<section class="space-y-5">
    <h1 class="text-3xl md:text-4xl font-bold">
        Leia ehitusmaterjalid targalt
    </h1>

    <form action="{{ route('listings.index') }}" method="GET" class="flex gap-2 max-w-2xl">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Otsi materjale..."
            class="w-full rounded-lg border border-neutral-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <button
            type="submit"
            class="rounded-lg bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"
        >
            Otsi
        </button>
    </form>
</section>