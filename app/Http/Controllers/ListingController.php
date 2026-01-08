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
            // ✅ uus: action (publish/draft)
            'action'      => ['nullable', 'in:publish,draft'],

            'title'       => ['required', 'string', 'max:140'],
            'description' => ['required', 'string', 'min:20'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price'       => ['nullable', 'numeric', 'min:0'],

            // PILDID
            'images'       => ['nullable', 'array', 'max:10'],
            'images.*'     => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
            'images_order' => ['nullable', 'string'], // JSON (järjekord)
        ]);

        $action = $validated['action'] ?? 'publish';
        $isDraft = $action === 'draft';

        DB::transaction(function () use ($request, $validated, $isDraft) {

            $listing = Listing::create([
                'user_id'      => $request->user()->id,
                'category_id'  => $validated['category_id'],
                'location_id'  => $validated['location_id'],
                'title'        => $validated['title'],
                'description'  => $validated['description'],
                'price'        => $validated['price'] ?? null,
                'currency'     => 'EUR',
                'listing_type' => 'sale',

                // ✅ mustand vs avaldamine
                'status'       => $isDraft ? 'draft' : 'published',
                'published_at' => $isDraft ? null : now(),
                'expires_at'   => $isDraft ? null : now()->addDays(30),
            ]);

            // --- Salvesta pildid, kui neid on ---
            $files = $request->file('images', []);

            if (!empty($files)) {
                $order = [];
                if ($request->filled('images_order')) {
                    $decoded = json_decode($request->input('images_order'), true);
                    if (is_array($decoded)) {
                        $order = $decoded;
                    }
                }

                if (count($order) !== count($files)) {
                    $order = range(0, count($files) - 1);
                }

                $sort = 0;
                foreach ($order as $fileIndex) {
                    if (!isset($files[$fileIndex])) {
                        continue;
                    }

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
            ->route('listings.mine')
            ->with('status', $isDraft ? 'Mustand salvestatud!' : 'Kuulutus avaldatud!');
    }

    // Minu kuulutused
    public function mine(Request $request)
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hasAnyListings = Listing::query()
            ->where('user_id', $request->user()->id)
            ->exists();

        $listingsQuery = Listing::query()
            ->where('user_id', $request->user()->id);

        $status = (string) $request->get('status', 'all');

        if ($status === 'active') {
            $listingsQuery->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                });

        } elseif ($status === 'expired') {
            $listingsQuery->where('status', 'published')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now());

        } elseif ($status === 'archived') {
            $listingsQuery->where('status', 'archived');

        } elseif ($status === 'sold') {
            $listingsQuery->where('status', 'sold');

        } elseif ($status === 'pending') {
            $listingsQuery->where('status', 'pending');

        } elseif ($status === 'draft') {
            // ✅ uus filter
            $listingsQuery->where('status', 'draft');

        } elseif ($status === 'all') {
            // ei filtreeri staatuse järgi
        } else {
            // safety: kui tuleb mingi tundmatu status, näita kõiki
        }

        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $listingsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $categoryId = $request->integer('category_id');
        if ($categoryId) {
            $listingsQuery->where('category_id', $categoryId);
        }

        $listings = $listingsQuery->latest('id')->get();

        return view('listings.mine', compact('listings', 'categories', 'hasAnyListings'));
    }

    public function showMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->load(['category', 'location', 'images']);

        return view('listings.mine-show', compact('listing'));
    }

    public function toggleMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        // kui on archived -> aktiveeri tagasi
        if ($listing->status === 'archived') {
            $listing->status = 'published';
            $listing->published_at = $listing->published_at ?? now();

            // kui aegunud või tühi, anna uus 30p
            if (!$listing->expires_at || $listing->expires_at->isPast()) {
                $listing->expires_at = now()->addDays(30);
            }

            $listing->save();

            return back()->with('status', 'Kuulutus aktiveeritud!');
        }

        // muul juhul -> peata (mitteaktiivne)
        $listing->status = 'archived';
        $listing->save();

        return back()->with('status', 'Kuulutus peatatud (mitteaktiivne).');
    }

    public function publishMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        // Ainult mustandit lubame "aktiveerida"
        if ($listing->status !== 'draft') {
            return back();
        }

        $listing->status = 'published';
        $listing->published_at = now();

        // anna aegumine kui puudu või juba minevikus
        if (!$listing->expires_at || $listing->expires_at->isPast()) {
            $listing->expires_at = now()->addDays(30);
        }

        $listing->save();

        return back()->with('status', 'Mustand avaldati (aktiveeriti).');
    }


    public function destroyMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->images()->delete();
        $listing->delete();

        return redirect()
            ->route('listings.mine')
            ->with('status', 'Kuulutus kustutatud.');
    }

    public function markSold(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->update([
            'status' => 'sold',
        ]);

        return back()->with('status', 'Kuulutus märgitud müüduks.');
    }

    public function markUnsold(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->update([
            'status'       => 'published',
            'published_at' => $listing->published_at ?? now(),
            'expires_at'   => now()->addDays(30),
        ]);

        return back()->with('status', 'Kuulutus taastatud müüki.');
    }

    public function editMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $listing->load(['category', 'location', 'images']);

        return view('listings.edit', compact('listing', 'categories'));
    }

    public function updateMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        // NB! praegu veel ilma piltideta (lisame järgmises sammus)
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:140'],
            'description' => ['required', 'string', 'min:20'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price'       => ['nullable', 'numeric', 'min:0'],
        ]);

        $listing->update([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'location_id' => $validated['location_id'],
            'price'       => $validated['price'] ?? null,
        ]);

        return redirect()
            ->route('listings.mine.show', $listing)
            ->with('status', 'Kuulutus muudetud!');
    }

}
