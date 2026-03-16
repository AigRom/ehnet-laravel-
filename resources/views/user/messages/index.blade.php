<x-layouts.app.public :title="__('Sõnumid')">

    <div class="mx-auto max-w-5xl px-4 py-6 md:py-8 space-y-6">

        <div>
            <h1 class="text-2xl font-bold">{{ __('Sõnumid') }}</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Sinu vestlused ostjate ja müüjatega.') }}
            </p>
        </div>

        @if($conversations->isEmpty())
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Sul ei ole veel ühtegi vestlust.') }}
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($conversations as $conversation)
                    @php
                        $lastMessage = $conversation->messages->first();
                        $otherUser = $conversation->seller_id === auth()->id()
                            ? $conversation->buyer
                            : $conversation->seller;
                    @endphp

                    <a
                        href="{{ route('messages.show', $conversation) }}"
                        class="block rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-blue-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-700"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1 min-w-0">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $conversation->listing->title ?? __('Kuulutus') }}
                                </div>

                                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ __('Vestlus kasutajaga:') }}
                                    {{ $otherUser->name ?? __('Tundmatu kasutaja') }}
                                </div>

                                @if($lastMessage)
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400 truncate">
                                        {{ \Illuminate\Support\Str::limit($lastMessage->body, 120) }}
                                    </div>
                                @endif
                            </div>

                            <div class="text-xs text-zinc-500 whitespace-nowrap shrink-0">
                                @if($lastMessage)
                                    {{ $lastMessage->created_at?->format('d.m.Y H:i') }}
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>

</x-layouts.app.public>