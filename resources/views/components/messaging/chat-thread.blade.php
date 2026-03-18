@props([
    'conversation',
])

<div class="flex-1 overflow-y-auto bg-zinc-50/70 px-4 py-5">
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
                        inline-block w-fit max-w-full rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm
                        {{ $isMine
                            ? 'bg-green-100 text-zinc-900 rounded-br-md'
                            : 'bg-white text-zinc-900 border border-zinc-200 rounded-bl-md'
                        }}
                    ">
                        @if(!empty($message->body))
                            <div class="{{ $message->attachments->isNotEmpty() ? 'mb-3' : '' }}">
                                {!! nl2br(e($message->body)) !!}
                            </div>
                        @endif

                        @if($message->attachments->isNotEmpty())
                            @php
                                $imageAttachments = $message->attachments->filter(fn ($attachment) => $attachment->type === 'image');
                                $fileAttachments = $message->attachments->filter(fn ($attachment) => $attachment->type !== 'image');
                            @endphp

                            <div class="space-y-3">
                                @if($imageAttachments->isNotEmpty())
                                    @php
                                        $imageCount = $imageAttachments->count();

                                        $imageGridCols = match (true) {
                                            $imageCount === 1 => 'grid-cols-1',
                                            $imageCount === 2 => 'grid-cols-2',
                                            default => 'grid-cols-2 sm:grid-cols-3',
                                        };

                                        $imageGridWidth = match (true) {
                                            $imageCount === 1 => 'w-[140px]',
                                            $imageCount === 2 => 'w-[220px]',
                                            default => 'w-[320px] sm:w-[360px]',
                                        };
                                    @endphp

                                    <div class="grid gap-2 {{ $imageGridCols }} {{ $imageGridWidth }} max-w-full">
                                        @foreach($imageAttachments as $attachment)
                                            <a
                                                href="{{ \Illuminate\Support\Facades\Storage::disk($attachment->disk)->url($attachment->path) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block overflow-hidden rounded-xl border border-zinc-200"
                                            >
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::disk($attachment->disk)->url($attachment->path) }}"
                                                    alt="{{ $attachment->original_name }}"
                                                    class="h-28 w-full object-cover"
                                                >
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                @if($fileAttachments->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach($fileAttachments as $attachment)
                                            <a
                                                href="{{ \Illuminate\Support\Facades\Storage::disk($attachment->disk)->url($attachment->path) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="flex items-center justify-between gap-3 rounded-xl bg-zinc-100 px-3 py-2 text-sm hover:bg-zinc-200"
                                            >
                                                <div class="min-w-0 flex-1">
                                                    <div class="truncate font-medium text-zinc-900">
                                                        {{ $attachment->original_name }}
                                                    </div>
                                                    <div class="text-xs text-zinc-500">
                                                        {{ number_format(($attachment->size ?? 0) / 1024, 1, ',', ' ') }} KB
                                                    </div>
                                                </div>

                                                <span class="shrink-0 text-xs font-medium text-blue-600">
                                                    {{ __('Ava') }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="mt-1 px-1 text-xs text-zinc-500 {{ $isMine ? 'text-right' : 'text-left' }}">
                        {{ $message->created_at?->format('d.m.Y H:i') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="flex h-full items-center justify-center">
                <div class="rounded-2xl border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-zinc-500">
                    {{ __('Vestlus on tühi. Alusta sõnumi saatmisest.') }}
                </div>
            </div>
        @endforelse
    </div>
</div>