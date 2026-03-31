<?php

namespace App\Http\Controllers\Trade;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Review;
use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Salvestab tehingu osapoole jäetud tagasiside.
     *
     * Tagasisidet saab jätta ainult siis, kui:
     * - tehing on completed
     * - ostja on kinnitanud kauba kättesaamise
     * - kasutaja on selle tehingu osapool
     * - sama kasutaja pole sellele tehingule juba tagasisidet jätnud
     */
    public function store(Request $request, Conversation $conversation, Trade $trade): RedirectResponse
    {
        abort_unless($trade->conversation_id === $conversation->id, 404);

        $user = $request->user();

        abort_unless($user, 403);
        abort_unless($trade->canBeReviewedBy($user), 403);

        if ($trade->hasReviewFrom($user)) {
            return back()->with('error', __('Oled sellele tehingule juba tagasiside jätnud.'));
        }

        $reviewedUser = $trade->reviewTargetFor($user);

        abort_if(! $reviewedUser, 403);
        abort_if($reviewedUser->id === $user->id, 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::create([
            'trade_id' => $trade->id,
            'reviewer_id' => $user->id,
            'reviewed_user_id' => $reviewedUser->id,
            'rating' => $validated['rating'],
            'comment' => filled($validated['comment'] ?? null)
                ? trim($validated['comment'])
                : null,
        ]);

        $this->createSystemMessage(
            $conversation->id,
            ($user->name ?? 'Kasutaja') . ' jättis tagasiside.',
            [
                'event' => 'review_left',
                'review_id' => $review->id,
                'trade_id' => $trade->id,
                'actor_user_id' => $user->id,
                'reviewed_user_id' => $reviewedUser->id,
            ]
        );

        $conversation->unhideForBoth();
        $conversation->touch();

        return back()->with('success', __('Tagasiside on edukalt salvestatud.'));
    }

    private function createSystemMessage(int $conversationId, string $body, array $meta = []): void
    {
        Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => null,
            'type' => Message::TYPE_SYSTEM,
            'body' => $body,
            'meta' => $meta ?: null,
        ]);
    }
}