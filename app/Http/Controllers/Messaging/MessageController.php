<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        // Ei luba kirjutada iseenda kuulutusele
        if ($user->id === $listing->user_id) {
            return back()->with('error', 'Enda kuulutusele ei saa sõnumit saata.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'seller_id' => $listing->user_id,
            'buyer_id' => $user->id,
        ]);

        $lastMessage = $conversation->messages()
            ->where('sender_id', $user->id)
            ->latest('id')
            ->first();

        if (
            $lastMessage &&
            $lastMessage->body === $validated['body'] &&
            $lastMessage->created_at !== null &&
            $lastMessage->created_at->gt(now()->subSeconds(5))
        ) {
            return back()->with('error', 'Sama sõnum saadeti juba.');
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => trim($validated['body']),
        ]);

        $conversation->touch();

        return back()->with('success', 'Sõnum saadeti edukalt.');

        
    }

    public function storeInConversation(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $conversation->seller_id === $user->id || $conversation->buyer_id === $user->id,
            403
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $lastMessage = $conversation->messages()
            ->where('sender_id', $user->id)
            ->latest('id')
            ->first();

        if (
            $lastMessage &&
            $lastMessage->body === trim($validated['body']) &&
            $lastMessage->created_at !== null &&
            $lastMessage->created_at->gt(now()->subSeconds(5))
        ) {
            return back()->with('error', 'Sama sõnum saadeti juba.');
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => trim($validated['body']),
        ]);

        $conversation->touch();

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Sõnum saadeti edukalt.');
    }


}