<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportMessageRequest;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    public function store(StoreSupportMessageRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $thread = DB::transaction(function () use ($validated, $user) {
            $thread = SupportThread::create([
                'user_id' => $user?->id,
                'name' => $user ? null : ($validated['name'] ?? null),
                'email' => $user ? $user->email : ($validated['email'] ?? null),
                'category' => $validated['category'],
                'subject' => $validated['subject'] ?? null,
                'status' => SupportThread::STATUS_NEW,
                'priority' => 'normal',
            ]);

            $thread->messages()->create([
                'user_id' => $user?->id,
                'sender_type' => SupportMessage::SENDER_USER,
                'message' => $validated['message'],
            ]);

            return $thread;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Aitäh! Sinu sõnum on vastu võetud.',
                'thread_id' => $thread->id,
            ]);
        }

        return back()->with('success', 'Aitäh! Sinu sõnum on vastu võetud.');
    }
}