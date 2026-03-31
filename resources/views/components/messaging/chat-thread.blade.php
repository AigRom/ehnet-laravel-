@props([
    'conversation',
])

<div
    x-data="chatScroll()"
    x-init="init()"
    @scroll="onScroll"
    class="flex-1 overflow-y-auto bg-zinc-50/70 px-4 py-5"
>
    <div class="space-y-4">
        @forelse($conversation->messages as $message)
            @php
                $isSystem = $message->isSystem();
                $isMine = ! $isSystem && $message->isFrom(auth()->user());

                $imageAttachments = $message->imageAttachments();
                $fileAttachments = $message->fileAttachments();

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

                $bubbleClasses = $isMine
                    ? 'rounded-br-md bg-green-100 text-zinc-900'
                    : 'rounded-bl-md border border-zinc-200 bg-white text-zinc-900';

                $metaAlignment = $isMine ? 'text-right' : 'text-left';
                $rowAlignment = $isMine ? 'justify-end' : 'justify-start';

                $review = $isSystem && data_get($message->meta, 'event') === 'review_left'
                    ? $message->relatedReview()
                    : null;

                $reviewModalState = 'showReviewModal' . $message->id;
            @endphp

            @if($isSystem)
                <div class="flex justify-center" x-data="{ {{ $reviewModalState }}: false }">
                    <div class="w-full max-w-2xl">
                        <div class="rounded-2xl border border-zinc-200 bg-white/90 px-4 py-3 text-center shadow-sm backdrop-blur">
                            <div class="mb-1 inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-zinc-700">
                                {{ __('Ehnet') }}
                            </div>

                            @if(!empty($message->body))
                                <div class="text-sm leading-6 text-zinc-700">
                                    {!! nl2br(e($message->body)) !!}
                                </div>
                            @endif

                            @if($review)
                                <div class="mt-3">
                                    <button
                                        type="button"
                                        @click="{{ $reviewModalState }} = true"
                                        class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 transition hover:bg-amber-100"
                                    >
                                        {{ __('Vaata tagasisidet') }}
                                    </button>
                                </div>

                                <x-reviews.show-modal
                                    :review="$review"
                                    :open-state="$reviewModalState"
                                />
                            @endif

                            <div class="mt-2 text-xs text-zinc-500">
                                {{ $message->created_at?->format('d.m.Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex {{ $rowAlignment }}">
                    <div class="max-w-[85%] md:max-w-[70%]">
                        <div class="mb-1 px-1 text-xs text-zinc-500 {{ $metaAlignment }}">
                            {{ $isMine ? __('Sina') : ($message->sender->name ?? __('Kasutaja')) }}
                        </div>

                        <div class="inline-block w-fit max-w-full rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm {{ $bubbleClasses }}">
                            @if(!empty($message->body))
                                <div class="{{ $message->hasAttachments() ? 'mb-3' : '' }}">
                                    {!! nl2br(e($message->body)) !!}
                                </div>
                            @endif

                            @if($message->hasAttachments())
                                <div class="space-y-3">
                                    @if($imageAttachments->isNotEmpty())
                                        <div class="grid max-w-full gap-2 {{ $imageGridCols }} {{ $imageGridWidth }}">
                                            @foreach($imageAttachments as $attachment)
                                                <a
                                                    href="{{ $attachment->url() }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="block overflow-hidden rounded-xl border border-zinc-200"
                                                >
                                                    <img
                                                        src="{{ $attachment->url() }}"
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
                                                    href="{{ $attachment->url() }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="flex items-center justify-between gap-3 rounded-xl bg-zinc-100 px-3 py-2 text-sm transition hover:bg-zinc-200"
                                                >
                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate font-medium text-zinc-900">
                                                            {{ $attachment->original_name }}
                                                        </div>
                                                        <div class="text-xs text-zinc-500">
                                                            {{ $attachment->sizeKb() }} KB
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

                        <div class="mt-1 px-1 text-xs text-zinc-500 {{ $metaAlignment }}">
                            {{ $message->created_at?->format('d.m.Y H:i') }}
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="flex h-full items-center justify-center">
                <div class="rounded-2xl border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-zinc-500">
                    {{ __('Vestlus on tühi. Alusta sõnumi saatmisest.') }}
                </div>
            </div>
        @endforelse

        <div x-ref="bottom"></div>
    </div>
</div>