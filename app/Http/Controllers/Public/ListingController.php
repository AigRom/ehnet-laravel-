<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Trade;
use App\Models\Location;

class ListingController extends Controller
{
    public function index()
    {
        $q = request('q');
        $sort = request('sort', 'newest');
        $category = request('category');
        $county = request('county');

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_et')
            ->get(['id', 'name_et', 'slug']);

        // EHAK järgi peaks maakond olema level 1.
        // Kui sul andmebaasis level erineb, muudame selle hiljem ära.
        $counties = Location::query()
            ->where('is_valid', true)
            ->where('level', 1)
            ->orderBy('name_et')
            ->get(['id', 'ehak_code', 'name_et']);

        $countyLocationIds = collect();

        if ($county) {
            $countyCode = (int) $county;

            // Omavalitsused maakonna all
            $municipalityCodes = Location::query()
                ->where('is_valid', true)
                ->where('parent_ehak_code', $countyCode)
                ->pluck('ehak_code');

            // Maakond ise + omavalitsused + nende all olevad asulad
            $countyLocationIds = Location::query()
                ->where('is_valid', true)
                ->where(function ($query) use ($countyCode, $municipalityCodes) {
                    $query->where('ehak_code', $countyCode)
                        ->orWhere('parent_ehak_code', $countyCode)
                        ->orWhereIn('parent_ehak_code', $municipalityCodes);
                })
                ->pluck('id');
        }

        $listings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'published')
                        ->where(function ($qq) {
                            $qq->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        });
                })->orWhere('status', 'reserved');
            })
            ->when($q, fn ($query) => $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            }))
            ->when($category, fn ($query) => $query->where('category_id', $category))
            ->when($county && $countyLocationIds->isNotEmpty(), fn ($query) => $query->whereIn('location_id', $countyLocationIds))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price', 'asc'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderBy('price', 'desc'))
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at', 'asc'))
            ->when($sort === 'newest', fn ($query) => $query->orderBy('created_at', 'desc'))
            ->paginate(36)
            ->withQueryString();

        return view('listings.index', compact(
            'listings',
            'q',
            'sort',
            'category',
            'county',
            'categories',
            'counties'
        ));
    }

    public function show(Listing $listing)
    {
        $isExpired = $listing->status === 'published'
            && $listing->expires_at
            && $listing->expires_at->isPast();

        abort_if(
            !in_array($listing->status, ['published', 'reserved'], true) || $isExpired,
            404
        );

        $listing->load([
            'category',
            'location',
            'images',
            'user.location',
        ]);

        $listing->user->loadCount([
            'listings as active_listings_count' => function ($query) {
                $query->where('status', 'published')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            },
        ]);

        $reservedTrade = Trade::query()
            ->with('buyer')
            ->where('listing_id', $listing->id)
            ->where('status', 'reserved')
            ->latest('id')
            ->first();

        $soldTrade = Trade::query()
            ->with('buyer')
            ->where('listing_id', $listing->id)
            ->where('status', 'completed')
            ->latest('id')
            ->first();

        $sellerListings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('id', '!=', $listing->id)
            ->where('user_id', $listing->user_id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'published')
                        ->where(function ($qq) {
                            $qq->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        });
                })->orWhere('status', 'reserved');
            })
            ->latest('created_at')
            ->limit(8)
            ->get();

        $similarListings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('id', '!=', $listing->id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'published')
                        ->where(function ($qq) {
                            $qq->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        });
                })->orWhere('status', 'reserved');
            })
            ->when($listing->category_id, fn ($query) => $query->where('category_id', $listing->category_id))
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('listings.show', compact(
            'listing',
            'sellerListings',
            'similarListings',
            'reservedTrade',
            'soldTrade',
        ));
    }
}