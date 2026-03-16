@php use Illuminate\Support\Facades\Storage; @endphp

<x-layouts.app.public :title="$listing->title">
    <div class="mx-auto max-w-4xl space-y-4">
        <a
            href="{{ route('listings.mine') }}"
            class="text-sm text-zinc-600 hover:underline dark:text-zinc-300"
        >
            ← {{ __('Tagasi') }}
        </a>

        <x-listings.detail :listing="$listing" />

        <div class="mt-6 flex flex-wrap justify-end gap-3">

            {{-- MUUDA --}}
            <a
                href="{{ route('listings.mine.edit', $listing) }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                {{ __('Muuda') }}
            </a>

            @php
                $isExpired = $listing->status === 'published'
                    && $listing->expires_at
                    && $listing->expires_at->isPast();
            @endphp

            {{-- MUSTAND: Aktiveeri --}}
            @if($listing->status === 'draft')
                <form method="POST" action="{{ route('listings.mine.publish', $listing) }}">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                    >
                        {{ __('Aktiveeri') }}
                    </button>
                </form>
            @endif

            {{-- ARCHIVED: Aktiveeri --}}
            @if($listing->status === 'archived')
                <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                    >
                        {{ __('Aktiveeri') }}
                    </button>
                </form>
            @endif

            {{-- PUBLISHED: aegunud -> Pane uuesti müüki, muidu -> Peata --}}
            @if($listing->status === 'published')
                @if($isExpired)
                    <form method="POST" action="{{ route('listings.mine.relist', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                        >
                            {{ __('Pane uuesti müüki') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            {{ __('Peata') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('listings.mine.sold', $listing) }}">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            {{ __('Märgi müüduks') }}
                        </button>
                    </form>
                @endif
            @endif

            {{-- SOLD: Taasta müüki --}}
            @if($listing->status === 'sold')
                <form method="POST" action="{{ route('listings.mine.unsold', $listing) }}">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        {{ __('Taasta müüki') }}
                    </button>
                </form>
            @endif

            {{-- KUSTUTA --}}
            <form
                method="POST"
                action="{{ route('listings.mine.destroy', $listing) }}"
                onsubmit="return confirm('{{ __('Kas oled kindel, et soovid kuulutuse kustutada?') }}')"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 dark:focus:ring-red-900/40"
                >
                    {{ __('Kustuta') }}
                </button>
            </form>

        </div>
    </div>
</x-layouts.app.public>