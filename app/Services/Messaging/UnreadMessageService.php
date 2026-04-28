<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Models\User;

class UnreadMessageService
{
    public function unreadConversationsCountFor(User $user): int
    {
        return Message::query()
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
            ->distinct()
            ->count('conversation_id');
    }
}