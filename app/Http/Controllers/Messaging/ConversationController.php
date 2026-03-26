<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
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
                'seller:id,name,created_at,avatar_path',
                'buyer:id,name,created_at,avatar_path',
                'latestMessage.sender:id,name,avatar_path',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                        ->whereNull('seller_hidden_at');
                })->orWhere(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->whereNull('buyer_hidden_at');
                });
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
            $conversation->hasParticipant($user),
            404
        );

        abort_if(
            $conversation->isHiddenFor($user),
            404
        );

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update([
                'read_at' => now(),
            ]);

        $conversation->load([
            'listing.images',
            'seller:id,name,created_at,avatar_path',
            'buyer:id,name,created_at,avatar_path',
            'messages.sender:id,name,avatar_path',
            'messages.attachments',
        ]);

        $conversations = Conversation::query()
            ->with([
                'listing',
                'listing.images',
                'seller:id,name,created_at,avatar_path',
                'buyer:id,name,created_at,avatar_path',
                'latestMessage.sender:id,name,avatar_path',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                        ->whereNull('seller_hidden_at');
                })->orWhere(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->whereNull('buyer_hidden_at');
                });
            })
            ->latest('updated_at')
            ->get();

        return view('user.messages.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
        ]);
    }

    public function openFromListing(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->id === $listing->user_id) {
            return back()->with('error', 'Enda kuulutusele ei saa sõnumit saata.');
        }

        if ($user->hasMessagingBlockWith($listing->user)) {
            return back()->with('error', 'Selle kasutajaga ei saa enam sõnumeid vahetada.');
        }

        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'seller_id' => $listing->user_id,
            'buyer_id' => $user->id,
        ]);

        $conversation->unhideFor($user);

        return redirect()->route('messages.show', $conversation);
    }

    public function destroy(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $conversation->hasParticipant($user),
            404
        );

        if (! $conversation->isHiddenFor($user)) {
            $conversation->hideFor($user);
        }

        return redirect()
            ->route('messages.index')
            ->with('success', 'Vestlus peideti sinu vaatest.');
    }
}