<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\MessageAttachment;

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

        if (!$request->filled('body') && !$request->hasFile('attachments')) {
            return back()->withErrors([
                'body' => 'Lisa sõnum või manus.',
            ]);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip',
            ],
        ]);

        $body = trim((string) ($validated['body'] ?? ''));

        $lastMessage = $conversation->messages()
            ->where('sender_id', $user->id)
            ->latest('id')
            ->first();

        if (
            $body !== '' &&
            $lastMessage &&
            $lastMessage->body === $body &&
            $lastMessage->created_at !== null &&
            $lastMessage->created_at->gt(now()->subSeconds(5))
        ) {
            return back()->with('error', 'Sama sõnum saadeti juba.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $body !== '' ? $body : null,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('messages/attachments', 'public');

                $mimeType = $file->getMimeType();
                $isImage = is_string($mimeType) && str_starts_with($mimeType, 'image/');

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize(),
                    'type' => $isImage ? 'image' : 'file',
                ]);
            }
        }

        $conversation->touch();

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Sõnum saadeti edukalt.');
    }


}