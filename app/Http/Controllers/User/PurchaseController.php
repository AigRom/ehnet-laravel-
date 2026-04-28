<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hasAnyPurchases = Trade::query()
            ->where('buyer_id', $user->id)
            ->whereNull('buyer_hidden_at')
            ->exists();

        /*
         * Kuvame ostude nimekirjas ainult iga kuulutuse viimase nähtava ostu-/tehingukirje.
         * buyer_hidden_at peidetud kirjeid enam nimekirja ega aktiivsesse paneeli ei too.
         */
        $latestTradeIds = Trade::query()
            ->where('buyer_id', $user->id)
            ->whereNull('buyer_hidden_at')
            ->selectRaw('MAX(id) as id')
            ->groupBy('listing_id');

        $purchasesQuery = Trade::query()
            ->whereIn('trades.id', $latestTradeIds)
            ->whereNull('trades.buyer_hidden_at')
            ->with($this->purchaseRelationsForBuyer());

        $status = (string) $request->get('status', 'all');

        if ($status === 'interest') {
            $purchasesQuery->where('trades.status', 'interest');
        } elseif ($status === 'reserved') {
            $purchasesQuery->where('trades.status', 'reserved');
        } elseif ($status === 'awaiting_confirmation') {
            $purchasesQuery->where(function ($query) {
                $query
                    ->where('trades.status', 'awaiting_confirmation')
                    ->orWhere(function ($q) {
                        $q->where('trades.status', 'completed')
                            ->whereNull('trades.buyer_confirmed_received_at');
                    });
            });
        } elseif ($status === 'completed') {
            $purchasesQuery
                ->where('trades.status', 'completed')
                ->whereNotNull('trades.buyer_confirmed_received_at');
        } elseif ($status === 'cancelled') {
            $purchasesQuery->where('trades.status', 'cancelled');
        }

        $q = trim((string) $request->get('q', ''));

        if ($q !== '') {
            $purchasesQuery->whereHas('listing', function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $categoryId = $request->integer('category_id');

        if ($categoryId) {
            $purchasesQuery->whereHas('listing', function ($sub) use ($categoryId) {
                $sub->where('category_id', $categoryId);
            });
        }

        /*
         * Aktiivne ost tuleb võtta enne sorteerimise join'e.
         */
        $activeTradeId = $request->integer('purchase');
        $activeTrade = null;

        if ($activeTradeId) {
            $activeTrade = (clone $purchasesQuery)
                ->where('trades.id', $activeTradeId)
                ->first();
        }

        $sort = (string) $request->get('sort', 'newest');

        if ($sort === 'oldest') {
            $purchasesQuery->orderBy('trades.id');
        } elseif ($sort === 'price_asc') {
            $purchasesQuery
                ->leftJoin('listings as sort_listings', 'sort_listings.id', '=', 'trades.listing_id')
                ->select('trades.*')
                ->orderByRaw('CASE WHEN sort_listings.price IS NULL THEN 1 ELSE 0 END')
                ->orderBy('sort_listings.price', 'asc')
                ->orderByDesc('trades.id');
        } elseif ($sort === 'price_desc') {
            $purchasesQuery
                ->leftJoin('listings as sort_listings', 'sort_listings.id', '=', 'trades.listing_id')
                ->select('trades.*')
                ->orderByRaw('CASE WHEN sort_listings.price IS NULL THEN 1 ELSE 0 END')
                ->orderBy('sort_listings.price', 'desc')
                ->orderByDesc('trades.id');
        } else {
            $purchasesQuery->latest('trades.id');
        }

        $purchases = $purchasesQuery
            ->paginate(24)
            ->withQueryString();

        if (! $activeTrade) {
            $activeTrade = $purchases
                ->getCollection()
                ->first();
        }

        if ($activeTrade) {
            $activeTrade->loadMissing($this->purchaseRelationsForBuyer());
        }

        return view('user.purchases.index', [
            'purchases' => $purchases,
            'categories' => $categories,
            'hasAnyPurchases' => $hasAnyPurchases,
            'currentStatus' => $status,
            'currentSort' => $sort,
            'currentQuery' => $q,
            'currentCategoryId' => $categoryId,
            'activeTrade' => $activeTrade,
        ]);
    }

    public function hide(Request $request, Trade $trade): RedirectResponse
    {
        $user = $request->user();

        abort_unless((int) $trade->buyer_id === (int) $user->id, 403);

        if ($trade->buyer_hidden_at !== null) {
            return redirect()
                ->route('purchases.index')
                ->with('success', 'Ost on juba kustutatud.');
        }

        if (! method_exists($trade, 'canBeHiddenByBuyer') || ! $trade->canBeHiddenByBuyer()) {
            return back()->withErrors([
                'trade' => 'Seda ostu ei saa praeguses olekus kustutada.',
            ]);
        }

        $trade->update([
            'buyer_hidden_at' => now(),
        ]);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Ost on kustutatud.');
    }

    public function show(Request $request, Trade $trade): View|RedirectResponse
    {
        $user = $request->user();

        abort_unless((int) $trade->buyer_id === (int) $user->id, 403);

        if ($trade->buyer_hidden_at !== null) {
            return redirect()
                ->route('purchases.index')
                ->withErrors([
                    'trade' => 'See ost on sinu vaatest kustutatud.',
                ]);
        }

        $trade->load($this->purchaseRelationsForBuyer());

        $listing = $trade->listing;

        $sellerListings = collect();
        $similarListings = collect();
        $reservedTrade = null;
        $soldTrade = null;

        if ($listing) {
            $listing->loadMissing([
                'user.location',
                'reservedTrade.buyer',
                'soldTrade.buyer',
                'latestActiveTrade.conversation',
            ]);

            if ($listing->user) {
                $listing->user->loadCount([
                    'listings as active_listings_count' => function ($query) {
                        $query->publicVisible();
                    },
                ]);
            }

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

            $reservedTrade = $listing->reservedTrade;
            $soldTrade = $listing->soldTrade;
        }

        return view('user.purchases.show', [
            'trade' => $trade,
            'listing' => $listing,
            'sellerListings' => $sellerListings,
            'similarListings' => $similarListings,
            'reservedTrade' => $reservedTrade,
            'soldTrade' => $soldTrade,
        ]);
    }

    private function purchaseRelationsForBuyer(): array
    {
        return [
            'listing',
            'listing.images',
            'listing.category',
            'listing.location',
            'listing.user.location',
            'listing.reservedTrade.buyer',
            'listing.soldTrade.buyer',
            'listing.latestActiveTrade.conversation',
            'seller',
            'buyer',
            'conversation',
            'reviews',
        ];
    }
}