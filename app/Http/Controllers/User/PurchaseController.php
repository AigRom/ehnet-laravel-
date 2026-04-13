<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Trade;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hasAnyPurchases = Trade::query()
            ->where('buyer_id', $user->id)
            ->exists();

        $latestTradeIds = Trade::query()
            ->where('buyer_id', $user->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('listing_id');

        $purchasesQuery = Trade::query()
            ->whereIn('id', $latestTradeIds)
            ->with([
                'listing.images',
                'listing.location',
                'listing.category',
                'seller',
                'conversation',
            ]);

        $status = (string) $request->get('status', 'all');

        if ($status === 'interest') {
            $purchasesQuery->where('status', 'interest');
        } elseif ($status === 'reserved') {
            $purchasesQuery->where('status', 'reserved');
        } elseif ($status === 'awaiting_confirmation') {
            $purchasesQuery
                ->where('status', 'completed')
                ->whereNull('buyer_confirmed_received_at');
        } elseif ($status === 'completed') {
            $purchasesQuery
                ->where('status', 'completed')
                ->whereNotNull('buyer_confirmed_received_at');
        } elseif ($status === 'cancelled') {
            $purchasesQuery->where('status', 'cancelled');
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

        $sort = (string) $request->get('sort', 'newest');

        if ($sort === 'oldest') {
            $purchasesQuery->orderBy('id');
        } elseif ($sort === 'price_asc') {
            $purchasesQuery->join('listings', 'trades.listing_id', '=', 'listings.id')
                ->orderByRaw('CASE WHEN listings.price IS NULL THEN 1 ELSE 0 END, listings.price ASC')
                ->select('trades.*');
        } elseif ($sort === 'price_desc') {
            $purchasesQuery->join('listings', 'trades.listing_id', '=', 'listings.id')
                ->orderByRaw('CASE WHEN listings.price IS NULL THEN 1 ELSE 0 END, listings.price DESC')
                ->select('trades.*');
        } else {
            $purchasesQuery->latest('id');
        }

        $purchases = $purchasesQuery
            ->paginate(24)
            ->withQueryString();

        return view('user.purchases.index', [
            'purchases' => $purchases,
            'categories' => $categories,
            'hasAnyPurchases' => $hasAnyPurchases,
            'currentStatus' => $status,
            'currentSort' => $sort,
            'currentQuery' => $q,
            'currentCategoryId' => $categoryId,
        ]);
    }

    public function show(Request $request, Trade $trade)
    {
        $user = $request->user();

        abort_unless($trade->buyer_id === $user->id, 403);

        $trade->load([
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
        ]);

        $listing = $trade->listing;

        abort_unless($listing, 404);

        $listing->user->loadCount([
            'listings as active_listings_count' => function ($query) {
                $query->publicVisible();
            },
        ]);

        $sellerListings = \App\Models\Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('id', '!=', $listing->id)
            ->where('user_id', $listing->user_id)
            ->publicVisible()
            ->latest('created_at')
            ->limit(8)
            ->get();

        $similarListings = \App\Models\Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('id', '!=', $listing->id)
            ->publicVisible()
            ->when($listing->category_id, fn ($query) => $query->where('category_id', $listing->category_id))
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('user.purchases.show', [
            'trade' => $trade,
            'listing' => $listing,
            'sellerListings' => $sellerListings,
            'similarListings' => $similarListings,
            'reservedTrade' => $listing->reservedTrade,
            'soldTrade' => $listing->soldTrade,
        ]);
    }
}