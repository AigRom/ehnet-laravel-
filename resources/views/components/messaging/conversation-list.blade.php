@props([
    'conversations' => collect(),
    'activeConversation' => null,
])

<div class="flex h-full min-h-[70vh] lg:min-h-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <div class="border-b border-zinc-200 px-4 py-4">
        <h2 class="text-base font-semibold text-zinc-900">
            {{ __('Vestlused') }}
        </h2>

    </div>

    <div class="flex-1 overflow-y-auto p-3 space-y-3">
        @forelse($conversations as $conversation)
            <x-messaging.conversation-list-item
                :conversation="$conversation"
                :active="$activeConversation && $activeConversation->id === $conversation->id"
            />
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-500">
                {{ __('Vestlusi ei leitud.') }}
            </div>
        @endforelse
    </div>
</div>