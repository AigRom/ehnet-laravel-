@php use Illuminate\Support\Facades\Storage; @endphp

<x-layouts.app.sidebar :title="$listing->title">
    <flux:main>
        <div class="max-w-4xl space-y-4">
            <a href="{{ route('listings.mine') }}" class="text-sm text-zinc-600 dark:text-zinc-300 hover:underline">
                ← {{ __('Tagasi') }}
            </a>

            <x-listings.detail
                :listing="$listing"
                :image-urls="$listing->images->map(fn($i) => Storage::url($i->path))->toArray()"
            />

            <div class="mt-6 flex flex-wrap gap-3 justify-end">

                {{-- MUUDA --}}
                <flux:button
                    variant="outline"
                    :href="route('listings.mine.edit', $listing)"
                    wire:navigate
                >
                    {{ __('Muuda') }}
                </flux:button>

                {{-- MUSTAND: Aktiveeri (publish) --}}
                @if($listing->status === 'draft')
                    <form method="POST" action="{{ route('listings.mine.publish', $listing) }}">
                        @csrf
                        @method('PATCH')
                        <flux:button type="submit" variant="primary">
                            {{ __('Aktiveeri') }}
                        </flux:button>
                    </form>
                @endif

                {{-- PEATA / AKTIVEERI (published <-> archived) --}}
                @if($listing->status === 'archived')
                    <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                        @csrf
                        @method('PATCH')
                        <flux:button type="submit" variant="primary">
                            {{ __('Aktiveeri') }}
                        </flux:button>
                    </form>
                @elseif($listing->status === 'published')
                    <form method="POST" action="{{ route('listings.mine.toggle', $listing) }}">
                        @csrf
                        @method('PATCH')
                        <flux:button type="submit" variant="outline">
                            {{ __('Peata') }}
                        </flux:button>
                    </form>
                @endif

                {{-- MÜÜDUD / TAASTA MÜÜKI (ainult published <-> sold) --}}
                @if($listing->status === 'published')
                    <form method="POST" action="{{ route('listings.mine.sold', $listing) }}">
                        @csrf
                        @method('PATCH')
                        <flux:button type="submit" variant="outline">
                            {{ __('Märgi müüduks') }}
                        </flux:button>
                    </form>
                @elseif($listing->status === 'sold')
                    <form method="POST" action="{{ route('listings.mine.unsold', $listing) }}">
                        @csrf
                        @method('PATCH')
                        <flux:button type="submit" variant="outline">
                            {{ __('Taasta müüki') }}
                        </flux:button>
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
                    <flux:button type="submit" variant="danger">
                        {{ __('Kustuta') }}
                    </flux:button>
                </form>

            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
