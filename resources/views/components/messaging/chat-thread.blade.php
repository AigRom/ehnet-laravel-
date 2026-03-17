@props([
    'conversation',
])

<div class="flex-1 overflow-y-auto bg-zinc-50/70 px-4 py-5 dark:bg-zinc-950/40 md:px-6">
    <div class="space-y-4">
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
</div>