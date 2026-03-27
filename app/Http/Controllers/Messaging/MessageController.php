<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function storeInConversation(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        // Kasutaja peab kuuluma vestlusesse
        abort_unless(
            $conversation->hasParticipant($user),
            404
        );

        $conversation->loadMissing('listing');

        // Kui kuulutus on kustutatud, siis uusi sõnumeid saata ei saa
        if ($conversation->listing && $conversation->listing->status === 'deleted') {
            return back()->with('error', 'Selles vestluses ei saa enam uusi sõnumeid saata.');
        }

        // Blokeering → ei saa saata
        if ($conversation->hasMessagingBlock($user)) {
            return back()->with('error', 'Selle kasutajaga ei saa enam sõnumeid vahetada.');
        }

        // Väldime täiesti tühja sõnumit
        if (!$request->filled('body') && !$request->hasFile('attachments')) {
            return back()->withErrors([
                'body' => 'Lisa sõnum või manus.',
            ]);
        }

        // Validatsioon
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240',
                'extensions:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip',
            ],
        ]);

        $body = trim((string) ($validated['body'] ?? ''));

        // Kaitse topeltsaatmise vastu
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

        // Sõnum
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $body !== '' ? $body : null,
        ]);

        // Manused
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

        // Vestlus uuesti nähtavaks mõlemale
        $conversation->unhideForBoth();

        // Tõsta vestlus üles listis
        $conversation->touch();

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Sõnum saadeti edukalt.');
    }
}