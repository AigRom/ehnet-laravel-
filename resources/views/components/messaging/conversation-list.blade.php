@props([
    'conversations' => collect(),
    'activeConversation' => null,
])

<div class="flex h-full min-h-[calc(100vh-6.5rem)] flex-col overflow-hidden bg-transparent lg:min-h-0 lg:rounded-2xl lg:border lg:border-zinc-200 lg:bg-white lg:shadow-sm">
    <div class="border-b border-zinc-200 px-3 py-3 sm:px-4 sm:py-4 lg:px-4 lg:py-4">
        <h2 class="text-sm font-semibold text-zinc-900 sm:text-base">
            {{ __('Vestlused') }}
        </h2>
    </div>

    <div class="flex-1 overflow-y-auto px-0 py-2 sm:px-1 sm:py-3 lg:p-3">
        <div class="space-y-2 sm:space-y-3">
            @forelse($conversations as $conversation)
                <x-messaging.conversation-list-item
                    :conversation="$conversation"
                    :active="$activeConversation?->id === $conversation->id"
                />
            @empty
                <div class="mx-3 rounded-lg border border-dashed border-zinc-300 px-3 py-6 text-center text-sm text-zinc-500 sm:mx-0 sm:rounded-xl sm:px-4 sm:py-8">
                    {{ __('Vestlusi ei leitud.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>