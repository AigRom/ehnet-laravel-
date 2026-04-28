<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = $this->visibleConversationsQuery($user)->get();

        return view('user.messages.index', [
            'conversations' => $conversations,
            'activeConversation' => $conversations->first(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_if($conversation->isHiddenFor($user), 404);

        $readColumn = $conversation->readColumnFor($user);

        $conversation->messages()
            ->whereNull($readColumn)
            ->where(function ($query) use ($user) {
                $this->applyReadableMessagesForUser($query, $user);
            })
            ->update([
                $readColumn => now(),
            ]);

        $conversation->load([
            'listing',
            'listing.images',
            'seller:id,name,company_name,company_reg_no,contact_first_name,contact_last_name,first_name,last_name,email,phone,created_at,avatar_path',
            'buyer:id,name,company_name,company_reg_no,contact_first_name,contact_last_name,first_name,last_name,email,phone,created_at,avatar_path',
            'messages.sender:id,name,avatar_path',
            'messages.attachments',
            'trades',
            'latestTrade',
            'latestOpenTrade',
        ]);

        return view('user.messages.show', [
            'conversation' => $conversation,
            'conversations' => $this->visibleConversationsQuery($user)->get(),
        ]);
    }

    public function showListing(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        abort_unless($conversation->hasParticipant($user), 404);
        abort_if($conversation->isHiddenFor($user), 404);

        $conversation->load([
            'listing',
            'listing.images',
            'listing.category',
            'listing.location',
            'listing.user.location',
            'listing.reservedTrade.buyer',
            'listing.awaitingConfirmationTrade.buyer',
            'listing.soldTrade.buyer',
            'listing.latestActiveTrade.conversation',
            'latestTrade',
            'latestOpenTrade',
        ]);

        $listing = $conversation->listing;

        abort_unless($listing, 404);

        $listing->user->loadCount([
            'listings as active_listings_count' => function ($query) {
                $query->publicVisible();
            },
        ]);

        $sellerListings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('id', '!=', $listing->id)
            ->where('user_id', $listing->user_id)
            ->publicVisible()
            ->latest('created_at')
            ->limit(8)
            ->get();

        $similarListings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('id', '!=', $listing->id)
            ->publicVisible()
            ->when($listing->category_id, fn ($query) => $query->where('category_id', $listing->category_id))
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('listings.show', [
            'listing' => $listing,
            'sellerListings' => $sellerListings,
            'similarListings' => $similarListings,
            'reservedTrade' => $listing->reservedTrade,
            'soldTrade' => $listing->soldTrade,
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

        if (! $listing->isActivePublished()) {
            return back()->with('error', 'Selle kuulutuse kohta ei saa enam uut vestlust alustada.');
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

        abort_unless($conversation->hasParticipant($user), 404);

        if (! $conversation->isHiddenFor($user)) {
            $conversation->hideFor($user);
        }

        return redirect()
            ->route('messages.index')
            ->with('success', 'Vestlus peideti sinu vaatest.');
    }

    private function visibleConversationsQuery(User $user): Builder
    {
        return Conversation::query()
            ->with([
                'listing',
                'listing.images',
                'seller:id,name,company_name,company_reg_no,contact_first_name,contact_last_name,first_name,last_name,email,phone,created_at,avatar_path',
                'buyer:id,name,company_name,company_reg_no,contact_first_name,contact_last_name,first_name,last_name,email,phone,created_at,avatar_path',
                'latestMessage.sender:id,name,avatar_path',
                'latestTrade',
                'latestOpenTrade',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    $this->applyUnreadMessagesForUser($query, $user);
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
            ->latest('updated_at');
    }

    private function applyUnreadMessagesForUser(Builder|HasMany $query, User $user): void
    {
        $query
            ->where(function ($q) use ($user) {
                $q->where(function ($sellerQuery) use ($user) {
                    $sellerQuery
                        ->whereHas('conversation', function ($conversationQuery) use ($user) {
                            $conversationQuery->where('seller_id', $user->id);
                        })
                        ->whereNull('seller_read_at');
                })->orWhere(function ($buyerQuery) use ($user) {
                    $buyerQuery
                        ->whereHas('conversation', function ($conversationQuery) use ($user) {
                            $conversationQuery->where('buyer_id', $user->id);
                        })
                        ->whereNull('buyer_read_at');
                });
            })
            ->where(function ($q) use ($user) {
                $this->applyReadableMessagesForUser($q, $user);
            });
    }

    private function applyReadableMessagesForUser(Builder|HasMany $query, User $user): void
    {
        $query
            ->where('type', Message::TYPE_SYSTEM)
            ->orWhere(function ($q) use ($user) {
                $q->where('type', Message::TYPE_USER)
                    ->where('sender_id', '!=', $user->id);
            });
    }
}