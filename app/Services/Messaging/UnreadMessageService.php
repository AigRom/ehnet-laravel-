<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Models\User;

class UnreadMessageService
{
    public function unreadConversationsCountFor(User $user): int
    {
        return count($this->unreadCountsByConversationFor($user));
    }

    public function unreadCountsByConversationFor(User $user): array
    {
        return Message::query()
            ->selectRaw('conversation_id, COUNT(*) as unread_count')
            ->where(function ($query) use ($user) {
                $query
                    ->where(function ($sellerQuery) use ($user) {
                        $sellerQuery
                            ->whereHas('conversation', function ($conversationQuery) use ($user) {
                                $conversationQuery
                                    ->where('seller_id', $user->id)
                                    ->whereNull('seller_hidden_at');
                            })
                            ->whereNull('seller_read_at');
                    })
                    ->orWhere(function ($buyerQuery) use ($user) {
                        $buyerQuery
                            ->whereHas('conversation', function ($conversationQuery) use ($user) {
                                $conversationQuery
                                    ->where('buyer_id', $user->id)
                                    ->whereNull('buyer_hidden_at');
                            })
                            ->whereNull('buyer_read_at');
                    });
            })
            ->where(function ($query) use ($user) {
                $query
                    ->where('type', Message::TYPE_SYSTEM)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('type', Message::TYPE_USER)
                            ->where('sender_id', '!=', $user->id);
                    });
            })
            ->groupBy('conversation_id')
            ->pluck('unread_count', 'conversation_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}