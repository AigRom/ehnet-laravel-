<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;

class ListingController extends Controller
{
    public function index()
    {
        $q = request('q');
        $sort = request('sort', 'newest');
        $category = request('category');

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_et')
            ->get(['id', 'name_et', 'slug']);

        $listings = Listing::query()
            ->where('status', 'published')
            ->where(function ($q2) {
                $q2->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            // SoftDeletes välistab deleted automaatselt (kui mudelis SoftDeletes)
            ->when($q, fn ($query) => $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            }))
            ->when($category, fn ($query) => $query->where('category_id', $category))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price', 'asc'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderBy('price', 'desc'))
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at', 'asc'))
            ->when($sort === 'newest', fn ($query) => $query->orderBy('created_at', 'desc'))
            ->paginate(24)
            ->withQueryString();

        return view('listings.index', compact('listings', 'q', 'sort', 'category', 'categories'));
    }

    public function show(Listing $listing)
    {
        $listing->load(['category', 'location', 'images']);

        abort_unless(
            $listing->status === 'published' &&
            (!$listing->expires_at || $listing->expires_at->isFuture()),
            404
        );

        return view('listings.show', compact('listing'));
    }
}
