@props([
    'conversations' => collect(),
    'activeConversation' => null,
])

<div class="flex h-full min-h-0 flex-col overflow-hidden bg-white lg:rounded-[1.75rem] lg:border lg:border-emerald-950/10 lg:shadow-xl lg:shadow-emerald-950/5">
    {{-- Header --}}
    <div class="shrink-0 border-b border-emerald-950/10 bg-white px-4 py-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-emerald-950">
                    {{ __('Vestlused') }}
                </h2>

                <p class="mt-0.5 text-sm font-medium text-zinc-500">
                    {{ trans_choice(':count vestlus|:count vestlust', $conversations->count(), ['count' => $conversations->count()]) }}
                </p>
            </div>

            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-900">
                <x-icons.chat-bubble class="h-5 w-5" />
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="min-h-0 flex-1 overflow-y-auto bg-stone-50/70 px-3 py-3 lg:bg-white lg:p-3">
        <div class="space-y-2.5">
            @forelse($conversations as $conversation)
                <x-messaging.conversation-list-item
                    :conversation="$conversation"
                    :active="$activeConversation?->id === $conversation->id"
                />
            @empty
                <div class="rounded-[1.5rem] border border-dashed border-emerald-950/15 bg-white px-5 py-10 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-900">
                        <x-icons.chat-bubble class="h-6 w-6" />
                    </div>

                    <h3 class="text-sm font-bold text-emerald-950">
                        {{ __('Vestlusi ei leitud') }}
                    </h3>

                    <p class="mt-1 text-sm leading-6 text-zinc-500">
                        {{ __('Sinu sõnumid ja tehingutega seotud vestlused ilmuvad siia.') }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>