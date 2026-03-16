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
                'listing:id,title',
                'seller:id,name',
                'buyer:id,name',
                'messages' => fn ($query) => $query->latest('created_at'),
            ])
            ->where(function ($query) use ($user) {
                $query->where('seller_id', $user->id)
                      ->orWhere('buyer_id', $user->id);
            })
            ->latest('updated_at')
            ->get();

        return view('user.messages.index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        abort_unless(
            $conversation->seller_id === $user->id || $conversation->buyer_id === $user->id,
            403
        );

        $conversation->load([
            'listing.images',
            'seller:id,name',
            'buyer:id,name',
            'messages.sender:id,name',
        ]);

        return view('user.messages.show', [
            'conversation' => $conversation,
        ]);
    }
}