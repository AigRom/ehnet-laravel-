<?php

namespace App\Http\Controllers\Trade;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function expressInterestFromListing(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->id === $listing->user_id) {
            return back()->with('error', 'Enda kuulutuse puhul ei saa ostusoovi esitada.');
        }

        if ($user->hasMessagingBlockWith($listing->user)) {
            return back()->with('error', 'Selle kasutajaga ei saa enam suhelda.');
        }

        if (! $listing->canAcceptTradeInterest()) {
            return back()->with('error', 'Selle kuulutuse puhul ei saa enam ostusoovi esitada.');
        }

        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'seller_id' => $listing->user_id,
            'buyer_id' => $user->id,
        ]);

        $conversation->unhideFor($user);

        if ($conversation->hasOpenTrade()) {
            return redirect()
                ->route('messages.show', $conversation)
                ->with('error', 'Selles vestluses on juba aktiivne ostusoov või broneering.');
        }

        DB::transaction(function () use ($conversation, $listing, $user) {
            Trade::create([
                'conversation_id' => $conversation->id,
                'listing_id' => $listing->id,
                'seller_id' => $conversation->seller_id,
                'buyer_id' => $conversation->buyer_id,
                'status' => 'interest',
            ]);

            $this->createSystemLikeMessage(
                $conversation->id,
                $user->id,
                'Soovin selle kuulutuse osta.'
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'Ostusoov saadeti müüjale.');
    }

    public function expressInterest(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_unless($conversation->isBuyer($user), 403);

        $listing = $conversation->listing;

        if (! $listing) {
            return back()->with('error', 'Kuulutus ei ole enam saadaval.');
        }

        if (! $listing->canAcceptTradeInterest()) {
            return back()->with('error', 'Selle kuulutuse puhul ei saa enam ostusoovi esitada.');
        }

        if ($conversation->hasOpenTrade()) {
            return back()->with('error', 'Selles vestluses on juba aktiivne ostusoov või broneering.');
        }

        DB::transaction(function () use ($conversation, $listing, $user) {
            Trade::create([
                'conversation_id' => $conversation->id,
                'listing_id' => $listing->id,
                'seller_id' => $conversation->seller_id,
                'buyer_id' => $conversation->buyer_id,
                'status' => 'interest',
            ]);

            $this->createSystemLikeMessage(
                $conversation->id,
                $user->id,
                'Soovin selle kuulutuse osta.'
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Ostusoov edastati müüjale.');
    }

    public function reserve(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_unless($conversation->isSeller($user), 403);

        $listing = $conversation->listing;

        if (! $listing) {
            return back()->with('error', 'Kuulutus ei ole enam saadaval.');
        }

        if (! $listing->canAcceptTradeReservation()) {
            return back()->with('error', 'Seda kuulutust ei saa enam broneerida.');
        }

        $trade = $conversation->latestInterestTrade()->first();

        if (! $trade) {
            return back()->with('error', 'Ostja ei ole veel ostusoovi esitanud.');
        }

        if (! $trade->canBeReserved()) {
            return back()->with('error', 'Seda tehingut ei saa enam broneerida.');
        }

        if ($listing->hasReservedTrade() && (! $listing->reservedTrade || $listing->reservedTrade->id !== $trade->id)) {
            return back()->with('error', 'Kuulutus on juba teisele ostjale broneeritud.');
        }

        DB::transaction(function () use ($conversation, $listing, $trade, $user) {
            $trade->update([
                'status' => 'reserved',
                'reserved_at' => now(),
                'contact_revealed_at' => now(),
            ]);

            $listing->update([
                'status' => 'reserved',
            ]);

            $this->createSystemLikeMessage(
                $conversation->id,
                $user->id,
                'Broneerisin kuulutuse sulle. Kontaktandmed on nüüd nähtavad mõlemale poolele.'
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Kuulutus broneeriti sellele ostjale.');
    }

    public function complete(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_unless($conversation->isSeller($user), 403);

        $listing = $conversation->listing;

        if (! $listing) {
            return back()->with('error', 'Kuulutus ei ole enam saadaval.');
        }

        $trade = $conversation->latestReservedTrade()->first();

        if (! $trade) {
            return back()->with('error', 'Enne tuleb kuulutus sellele ostjale broneerida.');
        }

        if (! $trade->canBeCompleted()) {
            return back()->with('error', 'Seda tehingut ei saa lõpetada.');
        }

        DB::transaction(function () use ($conversation, $listing, $trade, $user) {
            $trade->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $conversation->openTrades()
                ->where('id', '!=', $trade->id)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            $listing->update([
                'status' => 'sold',
                'sold_to_user_id' => $trade->buyer_id,
                'sold_trade_id' => $trade->id,
            ]);

            $this->createSystemLikeMessage(
                $conversation->id,
                $user->id,
                'Märkisin tehingu lõpetatuks. Kuulutus on nüüd müüdud. Ostja saab kinnitada kauba kättesaamise.'
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Kuulutus märgiti müüduks sellele ostjale.');
    }

    public function confirmReceived(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_unless($conversation->isBuyer($user), 403);

        $trade = $conversation->trades()
            ->where('status', 'completed')
            ->latest('id')
            ->first();

        if (! $trade) {
            return back()->with('error', 'Tehingut ei leitud.');
        }

        if (! $trade->canBeConfirmedByBuyer()) {
            return back()->with('error', 'Seda tehingut ei saa kinnitada.');
        }

        DB::transaction(function () use ($conversation, $trade, $user) {
            $trade->update([
                'buyer_confirmed_received_at' => now(),
            ]);

            $this->createSystemLikeMessage(
                $conversation->id,
                $user->id,
                'Kinnitasin, et kaup on kätte saadud.'
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Kinnitasid kauba kättesaamise.');
    }

    public function cancel(Request $request, Conversation $conversation, Trade $trade): RedirectResponse
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_unless($trade->conversation_id === $conversation->id, 404);

        if (! $conversation->isSeller($user) && ! $conversation->isBuyer($user)) {
            abort(403);
        }

        if (! $trade->canBeCancelled()) {
            return back()->with('error', 'Seda tehingut ei saa enam katkestada.');
        }

        $listing = $conversation->listing;

        DB::transaction(function () use ($conversation, $listing, $trade, $user) {
            $trade->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            if ($listing && $listing->isReserved() && ! $listing->hasReservedTrade()) {
                $listing->update([
                    'status' => 'published',
                ]);
            }

            $this->createSystemLikeMessage(
                $conversation->id,
                $user->id,
                'Tehingukatse katkestati.'
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Tehingukatse katkestati.');
    }

    private function createSystemLikeMessage(int $conversationId, int $senderId, string $body): void
    {
        Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
        ]);
    }
}