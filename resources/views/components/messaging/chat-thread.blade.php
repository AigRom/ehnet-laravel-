@props([
    'conversation',
])

<div
    x-data="chatScroll()"
    x-init="init()"
    @scroll="onScroll"
    class="min-h-0 flex-1 overflow-y-auto bg-gradient-to-b from-stone-50 via-emerald-50/20 to-stone-50 px-3 py-4 sm:px-5 sm:py-6"
>
    <div class="space-y-5">
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
                    $imageCount === 1 => 'w-full max-w-[300px] sm:max-w-[380px]',
                    $imageCount === 2 => 'w-full max-w-[420px] sm:max-w-[520px]',
                    default => 'w-full max-w-[520px] sm:max-w-[620px]',
                };

                $bubbleClasses = $isMine
                    ? 'bg-white text-zinc-900 ring-1 ring-emerald-200/90 shadow-sm shadow-emerald-950/5'
                    : 'bg-white text-zinc-900 ring-1 ring-zinc-200/90 shadow-sm shadow-zinc-950/5';

                $metaAlignment = $isMine ? 'text-right' : 'text-left';
                $rowAlignment = $isMine ? 'justify-end' : 'justify-start';

                $senderNameClass = $isMine
                    ? 'text-emerald-900/70'
                    : 'text-zinc-500';

                $timeClass = 'text-zinc-400';

                $messageTextClass = 'text-base leading-7 sm:text-[17px] sm:leading-8';

                $fileLinkClass = $isMine
                    ? 'bg-emerald-50 text-zinc-900 hover:bg-emerald-100/70'
                    : 'bg-zinc-100 text-zinc-900 hover:bg-zinc-200';

                $fileMetaClass = 'text-zinc-500';

                $fileActionClass = 'text-emerald-800';

                $review = $isSystem && data_get($message->meta, 'event') === 'review_left'
                    ? $message->relatedReview()
                    : null;

                $reviewModalState = 'showReviewModal' . $message->id;
            @endphp

            @if($isSystem)
                <div class="flex justify-center" x-data="{ {{ $reviewModalState }}: false }">
                    <div class="w-full max-w-md sm:max-w-lg">
                        <div class="rounded-2xl border border-emerald-950/10 bg-white/90 px-4 py-3 text-center shadow-sm backdrop-blur">
                            <div class="mb-2 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-900">
                                {{ __('EHNET') }}
                            </div>

                            @if(!empty($message->body))
                                <div class="text-[15px] font-semibold leading-7 text-zinc-700 sm:text-base">
                                    {!! nl2br(e($message->body)) !!}
                                </div>
                            @endif

                            @if($review)
                                <div class="mt-3">
                                    <button
                                        type="button"
                                        @click="{{ $reviewModalState }} = true"
                                        class="inline-flex items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-200/60"
                                    >
                                        {{ __('Vaata tagasisidet') }}
                                    </button>
                                </div>

                                <x-reviews.show-modal
                                    :review="$review"
                                    :open-state="$reviewModalState"
                                />
                            @endif

                            <div class="mt-2 text-xs font-medium text-zinc-400">
                                {{ $message->created_at?->format('d.m.Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex {{ $rowAlignment }}">
                    <div class="max-w-[90%] sm:max-w-[80%] md:max-w-[72%]">
                        <div class="mb-1 px-1 text-xs font-extrabold {{ $senderNameClass }} {{ $metaAlignment }}">
                            {{ $isMine ? __('Mina') : ($message->sender->name ?? __('Kasutaja')) }}
                        </div>

                        <div class="inline-block w-fit max-w-full rounded-2xl px-4 py-3 {{ $bubbleClasses }}">
                            @if(!empty($message->body))
                                <div class="{{ $message->hasAttachments() ? 'mb-3' : '' }} {{ $messageTextClass }}">
                                    {!! nl2br(e($message->body)) !!}
                                </div>
                            @endif

                            @if($message->hasAttachments())
                                <div class="space-y-3">
                                    @if($imageAttachments->isNotEmpty())
                                        <div class="grid max-w-full gap-2 {{ $imageGridCols }} {{ $imageGridWidth }}">
                                            @foreach($imageAttachments as $attachment)
                                                @php
                                                    $previewUrl = method_exists($attachment, 'thumbUrl')
                                                        ? $attachment->thumbUrl()
                                                        : $attachment->url();
                                                @endphp

                                                <a
                                                    href="{{ $attachment->url() }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="block overflow-hidden rounded-2xl bg-white/10"
                                                >
                                                    <img
                                                        src="{{ $previewUrl }}"
                                                        alt="{{ $attachment->original_name }}"
                                                        class="block h-44 w-full object-cover transition hover:scale-[1.02] sm:h-56"
                                                        loading="lazy"
                                                        decoding="async"
                                                    >
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($fileAttachments->isNotEmpty())
                                        <div class="space-y-2">
                                            @foreach($fileAttachments as $attachment)
                                                <a
                                                    href="{{ route('messages.attachments.download', $attachment) }}"
                                                    class="flex items-center justify-between gap-3 rounded-2xl px-3 py-2.5 text-sm transition {{ $fileLinkClass }}"
                                                >
                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate text-[15px] font-extrabold">
                                                            {{ $attachment->original_name }}
                                                        </div>

                                                        <div class="text-xs font-semibold {{ $fileMetaClass }}">
                                                            {{ $attachment->sizeKb() }} KB
                                                        </div>
                                                    </div>

                                                    <span class="shrink-0 text-xs font-extrabold {{ $fileActionClass }}">
                                                        {{ __('Laadi alla') }}
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="mt-1 px-1 text-xs font-semibold {{ $timeClass }} {{ $metaAlignment }}">
                            {{ $message->created_at?->format('d.m.Y H:i') }}
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="flex min-h-[320px] items-center justify-center">
                <div class="rounded-[1.75rem] border border-dashed border-emerald-950/15 bg-white/70 px-6 py-10 text-center shadow-sm backdrop-blur">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-900">
                        <x-icons.chat-bubble class="h-6 w-6" />
                    </div>

                    <div class="text-sm font-semibold text-emerald-950">
                        {{ __('Vestlus on tühi') }}
                    </div>

                    <div class="mt-1 text-sm text-zinc-500">
                        {{ __('Alusta sõnumi saatmisest.') }}
                    </div>
                </div>
            </div>
        @endforelse

        <div x-ref="bottom"></div>
    </div>
</div>