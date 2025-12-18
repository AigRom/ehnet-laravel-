<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{
    public function create()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // MVP: võtame alguses ainult level 2 ja 3 (vald/linn + asula/linnaosa)
        $locations = Location::query()
            ->where('is_valid', true)
            ->whereIn('level', [2, 3])
            ->orderBy('full_label_et')
            ->limit(200)
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
            'price'       => ['nullable', 'numeric', 'min:0'],

            // PILDID
            'images'      => ['nullable', 'array', 'max:10'],
            'images.*'    => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
            'images_order'=> ['nullable', 'string'], // JSON (järjekord)
        ]);

        DB::transaction(function () use ($request, $validated) {

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

            // --- Salvesta pildid, kui neid on ---
            $files = $request->file('images', []);

            if (!empty($files)) {
                // Ootame, et JS saadab indeksite järjekorra, nt: [2,0,1]
                $order = [];
                if ($request->filled('images_order')) {
                    $decoded = json_decode($request->input('images_order'), true);
                    if (is_array($decoded)) {
                        $order = $decoded;
                    }
                }

                // Kui order puudub/vigane, kasuta vaikimisi 0..n-1
                if (count($order) !== count($files)) {
                    $order = range(0, count($files) - 1);
                }

                $sort = 0;
                foreach ($order as $fileIndex) {
                    if (!isset($files[$fileIndex])) {
                        continue;
                    }

                    // Salvestab: storage/app/public/listings/...
                    $path = $files[$fileIndex]->store('listings', 'public');

                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path'       => $path,
                        'sort_order' => $sort,
                    ]);

                    $sort++;
                }
            }
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'Kuulutus lisatud!');
    }
}
