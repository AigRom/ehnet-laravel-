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
            $trade = Trade::create([
                'conversation_id' => $conversation->id,
                'listing_id' => $listing->id,
                'seller_id' => $conversation->seller_id,
                'buyer_id' => $conversation->buyer_id,
                'status' => 'interest',
            ]);

            $this->createSystemMessage(
                $conversation->id,
                ($conversation->buyer?->name ?? 'Kasutaja') . ' saatis sellele kuulutusele ostusoovi.',
                [
                    'event' => 'trade_interest_created',
                    'trade_id' => $trade->id,
                    'listing_id' => $listing->id,
                    'actor_user_id' => $user->id,
                ]
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
            $trade = Trade::create([
                'conversation_id' => $conversation->id,
                'listing_id' => $listing->id,
                'seller_id' => $conversation->seller_id,
                'buyer_id' => $conversation->buyer_id,
                'status' => 'interest',
            ]);

            $this->createSystemMessage(
                $conversation->id,
                ($conversation->buyer?->name ?? 'Kasutaja') . ' saatis sellele kuulutusele ostusoovi.',
                [
                    'event' => 'trade_interest_created',
                    'trade_id' => $trade->id,
                    'listing_id' => $listing->id,
                    'actor_user_id' => $user->id,
                ]
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

            $this->createSystemMessage(
                $conversation->id,
                ($conversation->seller?->name ?? 'Kasutaja') . ' broneeris kuulutuse sellele ostjale. Kontaktandmed on nüüd nähtavad mõlemale poolele.',
                [
                    'event' => 'trade_reserved',
                    'trade_id' => $trade->id,
                    'listing_id' => $listing->id,
                    'actor_user_id' => $user->id,
                ]
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

        if (! $trade->canBeMarkedAsHandedOver()) {
            return back()->with('error', 'Seda tehingut ei saa sellesse etappi viia.');
        }

        DB::transaction(function () use ($conversation, $trade, $user) {
            $trade->update([
                'status' => 'awaiting_confirmation',
                'awaiting_confirmation_at' => now(),
            ]);

            $this->createSystemMessage(
                $conversation->id,
                ($conversation->seller?->name ?? 'Kasutaja') . ' märkis, et kaup on üle antud või teele pandud. Ostja saab nüüd kinnitada kauba kättesaamise.',
                [
                    'event' => 'trade_awaiting_confirmation',
                    'trade_id' => $trade->id,
                    'listing_id' => $trade->listing_id,
                    'actor_user_id' => $user->id,
                ]
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Tehing märgiti ostja kinnituse ootele.');
    }

    public function confirmReceived(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_unless($conversation->isBuyer($user), 403);

        $trade = $conversation->trades()
            ->where('status', 'awaiting_confirmation')
            ->latest('id')
            ->first();

        if (! $trade) {
            return back()->with('error', 'Ootel kinnitusega tehingut ei leitud.');
        }

        if (! $trade->canBeConfirmedByBuyer()) {
            return back()->with('error', 'Seda tehingut ei saa kinnitada.');
        }

        DB::transaction(function () use ($conversation, $trade, $user) {
            $trade->update([
                'status' => 'completed',
                'completed_at' => now(),
                'buyer_confirmed_received_at' => now(),
            ]);

            $trade->listing?->update([
                'status' => 'sold',
                'sold_to_user_id' => $trade->buyer_id,
                'sold_trade_id' => $trade->id,
            ]);

            $conversation->openTrades()
                ->where('id', '!=', $trade->id)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            $this->createSystemMessage(
                $conversation->id,
                ($conversation->buyer?->name ?? 'Kasutaja') . ' kinnitas, et kaup on kätte saadud. Tehing on nüüd lõpetatud.',
                [
                    'event' => 'trade_received_confirmed',
                    'trade_id' => $trade->id,
                    'listing_id' => $trade->listing_id,
                    'actor_user_id' => $user->id,
                ]
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
            $previousStatus = $trade->status;

            $trade->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            if ($listing && $listing->isReserved() && ! $listing->hasReservedTrade()) {
                $listing->update([
                    'status' => 'published',
                ]);
            }

            $cancelMessage = match ($previousStatus) {
                'interest' => $conversation->isSeller($user)
                    ? 'Müüja lükkas ostusoovi tagasi.'
                    : 'Ostja võttis ostusoovi tagasi.',
                'reserved' => 'Broneering tühistati.',
                'awaiting_confirmation' => 'Tehing katkestati enne ostja kinnitust.',
                default => 'Tehing katkestati.',
            };

            $this->createSystemMessage(
                $conversation->id,
                $cancelMessage,
                [
                    'event' => 'trade_cancelled',
                    'trade_id' => $trade->id,
                    'listing_id' => $listing?->id,
                    'actor_user_id' => $user->id,
                ]
            );

            $conversation->unhideForBoth();
            $conversation->touch();
        });

        return back()->with('success', 'Tehing katkestati.');
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