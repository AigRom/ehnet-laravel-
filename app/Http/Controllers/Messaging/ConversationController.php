<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

use Illuminate\View\View;

class ConversationController extends Controller
{
        public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->with([
                'listing',
                'listing.images',
                'seller:id,name,created_at',
                'buyer:id,name,created_at',
                'latestMessage.sender:id,name',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            ->where(function ($query) use ($user) {
                $query->where('seller_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })
            ->latest('updated_at')
            ->get();

        $activeConversation = $conversations->first();

        return view('user.messages.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        abort_unless(
            $conversation->seller_id === $user->id || $conversation->buyer_id === $user->id,
            403
        );

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update([
                'read_at' => now(),
            ]);

        $conversation->load([
            'listing.images',
            'seller:id,name,created_at',
            'buyer:id,name,created_at',
            'messages.sender:id,name',
            'messages.attachments',
        ]);

        $conversations = Conversation::query()
            ->with([
                'listing',
                'listing.images',
                'seller:id,name,created_at',
                'buyer:id,name,created_at',
                'latestMessage.sender:id,name',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            ->where(function ($query) use ($user) {
                $query->where('seller_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })
            ->latest('updated_at')
            ->get();

        return view('user.messages.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
        ]);
    }
}