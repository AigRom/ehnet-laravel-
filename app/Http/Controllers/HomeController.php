<?php

namespace App\Http\Controllers;

use App\Models\Listing;

class HomeController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_et')
            ->get(['id', 'name_et']);
        $featured = Listing::query()
            ->homeFeed()
            ->with(['category', 'location', 'images'])
            ->limit(4)
            ->get();
        $latest = Listing::query()
            ->homeFeed()
            ->with(['category', 'location', 'images'])
            ->limit(12)
            ->get();

        return view('home', compact('featured', 'latest', 'categories'));
    }
}
