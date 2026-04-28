<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Messaging\MessageAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MessageController extends Controller
{
    public function storeInConversation(
        Request $request,
        Conversation $conversation,
        MessageAttachmentService $attachmentService
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);

        $conversation->loadMissing('listing');

        if (! $conversation->canUserSendMessages($user)) {
            return back()->with('error', 'Selles vestluses ei saa enam uusi sõnumeid saata.');
        }

        if ($conversation->hasMessagingBlock($user)) {
            return back()->with('error', 'Selle kasutajaga ei saa enam sõnumeid vahetada.');
        }

        if (! $request->filled('body') && ! $request->hasFile('attachments')) {
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

        $isDuplicateRecentTextMessage =
            $body !== ''
            && $lastMessage
            && $lastMessage->body === $body
            && $lastMessage->created_at !== null
            && $lastMessage->created_at->gt(now()->subSeconds(5));

        if ($isDuplicateRecentTextMessage) {
            return back()->with('error', 'Sama sõnum saadeti juba.');
        }

        $storedFiles = [];

        try {
            DB::transaction(function () use (
                $request,
                $conversation,
                $user,
                $body,
                $attachmentService,
                &$storedFiles
            ) {
                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id,
                    'type' => Message::TYPE_USER,
                    'body' => $body !== '' ? $body : null,
                ]);

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $attachment = $attachmentService->store($file, $message->id);

                        $storedFiles[] = [
                            'disk' => $attachment->disk,
                            'path' => $attachment->path,
                        ];

                        if ($attachment->thumb_path) {
                            $storedFiles[] = [
                                'disk' => $attachment->disk,
                                'path' => $attachment->thumb_path,
                            ];
                        }
                    }
                }

                $conversation->unhideForBoth();
                $conversation->touch();
            });
        } catch (Throwable $e) {
            foreach ($storedFiles as $storedFile) {
                Storage::disk($storedFile['disk'])->delete($storedFile['path']);
            }

            throw $e;
        }

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Sõnum saadeti edukalt.');
    }
}