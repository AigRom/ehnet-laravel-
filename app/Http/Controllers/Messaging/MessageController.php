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
    /**
     * Salvestab uue sõnumi olemasolevasse vestlusesse.
     *
     * Lubatud variandid:
     * - ainult tekst
     * - tekst + manused
     * - ainult manused
     *
     * Kui vestlus oli peidetud, siis uue sõnumi saatmine muudab selle
     * uuesti nähtavaks mõlemale osapoolele.
     *
     * Kui kasutajate vahel on sõnumiblokk, siis uut sõnumit saata ei saa.
     */
    public function storeInConversation(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        // Kontrollime, et kasutaja kuulub sellesse vestlusesse
        abort_unless(
            $conversation->hasParticipant($user),
            404
        );

        // Kui selle vestluse kahe osapoole vahel on blokk,
        // siis uusi sõnumeid saata ei tohi
        if ($conversation->hasMessagingBlock($user)) {
            return back()->with('error', 'Selle kasutajaga ei saa enam sõnumeid vahetada.');
        }

        // Väldime tühja sõnumi saatmist:
        // vähemalt tekst või vähemalt üks manus peab olemas olema
        if (!$request->filled('body') && !$request->hasFile('attachments')) {
            return back()->withErrors([
                'body' => 'Lisa sõnum või manus.',
            ]);
        }

        // Valideerime sisendi:
        // - body võib olla tühi/null
        // - manuseid võib olla kuni 5
        // - üksiku faili max suurus 10 MB
        // - lubatud faililaiendid on piiratud
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240',
                'extensions:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip',
            ],
        ]);

        // Eemaldame body ümbert tühikud
        $body = trim((string) ($validated['body'] ?? ''));

        // Väike kaitse topeltkliki / topeltsaatmise vastu:
        // kui sama kasutaja saatis viimase 5 sekundi jooksul täpselt sama body,
        // siis uut sõnumit ei loo
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

        // Loome sõnumi.
        // Kui body jäi tühjaks, salvestame nulli,
        // et toetada "ainult manused" kasutusjuhtu.
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $body !== '' ? $body : null,
        ]);

        // Salvestame manused, kui need on kaasa pandud
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

        // Uus sõnum muudab vestluse uuesti aktiivseks mõlemale poolele:
        // - seller_hidden_at = null
        // - buyer_hidden_at = null
        // - fully_hidden_at = null
        $conversation->unhideForBoth();

        // Uuendame vestluse updated_at välja,
        // et vestlus liiguks nimekirjas ettepoole
        $conversation->touch();

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Sõnum saadeti edukalt.');
    }
}