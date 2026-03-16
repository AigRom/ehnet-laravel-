<x-layouts.app.public :title="__('Vestlus')">

    @php
        $isSeller = auth()->id() === $conversation->seller_id;
        $otherUser = $isSeller ? $conversation->buyer : $conversation->seller;
        $listing = $conversation->listing;
        $coverImage = $listing?->coverImageUrl();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 md:py-8">
        <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">

            {{-- Vasak kontekstipaneel --}}
            <aside class="space-y-4">

                <a
                    href="{{ route('messages.index') }}"
                    class="inline-flex items-center text-sm text-blue-600 hover:underline"
                >
                    ← {{ __('Tagasi sõnumitesse') }}
                </a>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="aspect-[4/3] bg-zinc-100 dark:bg-zinc-800">
                        @if($coverImage)
                            <img
                                src="{{ $coverImage }}"
                                alt="{{ $listing->title }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-zinc-500">
                                {{ __('Pilt puudub') }}
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3 p-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">
                                {{ __('Kuulutus') }}
                            </div>
                            <h1 class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $listing->title }}
                            </h1>
                        </div>

                        <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-800/70">
                            <div class="text-xs text-zinc-500">{{ __('Hind') }}</div>
                            <div class="mt-1 text-base font-semibold">
                                @if(!is_null($listing->price))
                                    {{ number_format((float) $listing->price, 2, ',', ' ') }} {{ $listing->currency ?? '€' }}
                                @else
                                    {{ __('Kokkuleppel') }}
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-zinc-500">{{ __('Vestlus kasutajaga:') }}</span>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                                </div>
                            </div>

                            <div>
                                <span class="text-zinc-500">{{ __('Roll:') }}</span>
                                <div class="text-zinc-900 dark:text-zinc-100">
                                    {{ $isSeller ? __('Ostja') : __('Müüja') }}
                                </div>
                            </div>
                        </div>

                        <a
                            href="{{ route('listings.show', $listing) }}"
                            class="inline-flex items-center text-sm text-blue-600 hover:underline"
                        >
                            {{ __('Vaata kuulutust') }}
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Chat ala --}}
            <section class="flex min-h-[70vh] flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                {{-- Päis --}}
                <div class="border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 md:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm text-zinc-500">
                                {{ __('Vestlus') }}
                            </div>
                            <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                            </div>
                        </div>

                        <div class="text-xs text-zinc-500 text-right">
                            {{ __('Kuulutus:') }}<br>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                {{ \Illuminate\Support\Str::limit($listing->title, 40) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Sõnumid --}}
                <div class="flex-1 space-y-4 overflow-y-auto bg-zinc-50/70 px-4 py-5 dark:bg-zinc-950/40 md:px-6">

                    @forelse($conversation->messages as $message)
                        @php
                            $isMine = $message->sender_id === auth()->id();
                        @endphp

                        <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[85%] md:max-w-[70%]">
                                <div class="mb-1 px-1 text-xs text-zinc-500 {{ $isMine ? 'text-right' : 'text-left' }}">
                                    {{ $isMine ? __('Sina') : ($message->sender->name ?? __('Kasutaja')) }}
                                </div>

                                <div class="
                                    rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm
                                    {{ $isMine
                                        ? 'bg-blue-600 text-white rounded-br-md'
                                        : 'bg-white text-zinc-900 border border-zinc-200 rounded-bl-md dark:bg-zinc-900 dark:text-zinc-100 dark:border-zinc-800'
                                    }}
                                ">
                                    {!! nl2br(e($message->body)) !!}
                                </div>

                                <div class="mt-1 px-1 text-xs text-zinc-500 {{ $isMine ? 'text-right' : 'text-left' }}">
                                    {{ $message->created_at?->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center">
                            <div class="rounded-2xl border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-zinc-500 dark:border-zinc-700">
                                {{ __('Vestlus on tühi. Alusta sõnumi saatmisest.') }}
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Sisestusala --}}
                <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 md:p-5">
                    <form method="POST" action="{{ route('messages.store', $conversation) }}" class="space-y-3">
                        @csrf

                        <div>
                            <label for="body" class="sr-only">{{ __('Sõnum') }}</label>
                            <textarea
                                id="body"
                                name="body"
                                rows="4"
                                required
                                placeholder="{{ __('Kirjuta vastus...') }}"
                                class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                            >{{ old('body') }}</textarea>

                            @error('body')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs text-zinc-500">
                                {{ __('Sõnum saadetakse EHNETi vestluse kaudu.') }}
                            </p>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                            >
                                {{ __('Saada') }}
                            </button>
                        </div>
                    </form>
                </div>

            </section>
        </div>
    </div>

</x-layouts.app.public>