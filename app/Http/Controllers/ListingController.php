<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function create()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // MVP: võtame alguses ainult level 2 ja 3 (vald/linn + asula/linnaosa)
        // et dropdown ei oleks kohe liiga suur.
        $locations = Location::query()
            ->where('is_valid', true)
            ->whereIn('level', [2, 3])
            ->orderBy('full_label_et')
            ->limit(200) // ajutine – kuni autocomplete tuleb
            ->get();

        return view('listings.create', compact('categories', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:140'],
            'description' => ['required', 'string', 'min:20'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price'       => ['nullable', 'numeric', 'min:0'], // 0 = tasuta, null = kokkuleppel
        ]);

        $listing = Listing::create([
            'user_id'      => $request->user()->id,
            'category_id'  => $validated['category_id'],
            'location_id'  => $validated['location_id'],
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'price'        => $validated['price'] ?? null,
            'currency'     => 'EUR',
            'listing_type' => 'sale',
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Kuulutus lisatud!');
    }
}
