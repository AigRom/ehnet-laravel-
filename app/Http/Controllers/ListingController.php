<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{

    public function index(Request $request)
    {
        $q = trim((string) $request->get('search', $request->get('q', '')));

        $listings = Listing::query()
            ->homeFeed()
            ->with(['category', 'location', 'images'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->paginate(24)
            ->withQueryString();

        return view('listings.index', compact('listings', 'q'));
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



    public function create()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // MVP: level 2 ja 3 (vald/linn + asula/linnaosa)
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
        $action  = (string) $request->input('action', 'publish');
        $isDraft = $action === 'draft';

        // price_mode -> price normaliseerimine enne validate
        $this->normalizePriceMode($request);

        $rulesDraft = [
            'action'      => ['nullable', 'in:publish,draft'],

            // draft: kõik võivad puududa
            'title'       => ['nullable', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'price_mode'  => ['nullable', 'in:deal,free,price'],

            // ✅ Seisukord
            'condition'   => ['nullable', 'in:new,used,leftover'],

            // ✅ Kättesaamine
            'delivery_options'   => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],

            // Pildid (create)
            'images'       => ['nullable', 'array', 'max:10'],
            'images.*'     => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images_order' => ['nullable', 'string'],
        ];

        $rulesPublish = [
            'action'      => ['nullable', 'in:publish,draft'],

            // publish: tuumik
            'title'       => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'], // ✅ valikuline
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'price_mode'  => ['nullable', 'in:deal,free,price'],

            'condition'   => ['nullable', 'in:new,used,leftover'],

            'delivery_options'   => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],

            'images'       => ['nullable', 'array', 'max:10'],
            'images.*'     => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images_order' => ['nullable', 'string'],
        ];

        $validated = $request->validate($isDraft ? $rulesDraft : $rulesPublish);

        // STOP: ära loo täiesti tühja mustandit
        if ($isDraft) {
            $hasAny =
                filled($validated['title'] ?? null) ||
                filled($validated['description'] ?? null) ||
                filled($validated['category_id'] ?? null) ||
                filled($validated['location_id'] ?? null) ||
                filled($validated['condition'] ?? null) ||
                !empty($validated['delivery_options'] ?? []) ||
                ($request->filled('price') && $request->input('price') !== null) ||
                !empty($request->file('images', []));

            if (!$hasAny) {
                throw ValidationException::withMessages([
                    'title' => 'Täiesti tühja mustandit ei salvestata. Lisa vähemalt pealkiri, kategooria, asukoht, hind, seisukord, kättesaamine või pilt.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $validated, $isDraft) {
            $delivery = array_values(array_unique($validated['delivery_options'] ?? []));

            $listing = Listing::create([
                'user_id'      => $request->user()->id,
                'category_id'  => $validated['category_id'] ?? null,
                'location_id'  => $validated['location_id'] ?? null,
                'title'        => $validated['title'] ?? null,
                'description'  => $validated['description'] ?? null,
                'price'        => array_key_exists('price', $validated) ? $validated['price'] : null,
                'currency'     => 'EUR',
                'listing_type' => 'sale',

                // ✅ sama loogika nagu condition: salvestame väärtuse otse
                'condition'       => $validated['condition'] ?? null,
                'delivery_options'=> $delivery, // ✅ array, cast teeb JSONiks

                'status'       => $isDraft ? 'draft' : 'published',
                'published_at' => $isDraft ? null : now(),
                'expires_at'   => $isDraft ? null : now()->addDays(30),
            ]);

            // --- Pildid (create) ---
            $files = $request->file('images', []);
            if (!empty($files)) {
                $order = $this->safeJsonArray($request->input('images_order'));

                $order = array_values(array_filter(array_map(
                    fn ($x) => is_numeric($x) ? (int) $x : null,
                    $order
                ), fn ($x) => $x !== null));

                if (count($order) !== count($files)) {
                    $order = range(0, count($files) - 1);
                }

                $sort = 0;
                foreach ($order as $fileIndex) {
                    if (!isset($files[$fileIndex])) continue;

                    $path = $files[$fileIndex]->store('listings', 'public');

                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path'       => $path,
                        'sort_order' => $sort++,
                    ]);
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
            $listingsQuery->where('status', 'draft');
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

        $this->normalizePriceMode($request);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'], // ✅ valikuline
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'price_mode'  => ['nullable', 'in:deal,free,price'],

            'condition'   => ['nullable', 'in:new,used,leftover'],

            'delivery_options'   => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],

            // PILDID (edit)
            'new_images'   => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'deleted_image_ids' => ['nullable', 'string'],
            'images_order'      => ['nullable', 'string'], // JSON: ["e:12","n:0",...]
        ]);

        DB::transaction(function () use ($request, $listing, $validated) {

            $delivery = array_values(array_unique($validated['delivery_options'] ?? []));

            // 1) põhiandmed
            $listing->update([
                'title'          => $validated['title'],
                'description'    => $validated['description'] ?? null,
                'category_id'    => $validated['category_id'],
                'location_id'    => $validated['location_id'],
                'price'          => array_key_exists('price', $validated) ? $validated['price'] : null,

                'condition'       => $validated['condition'] ?? null,
                'delivery_options'=> $delivery, // ✅ array
            ]);

            // 2) JSON parse
            $deletedIds = $this->safeJsonArray($request->input('deleted_image_ids'));
            $deletedIds = array_values(array_filter(array_map('intval', $deletedIds)));

            $mixOrder = $this->safeJsonArray($request->input('images_order')); // ["e:..","n:.."]

            // 3) delete existing images
            if (!empty($deletedIds)) {
                $toDelete = $listing->images()->whereIn('id', $deletedIds)->get();

                foreach ($toDelete as $img) {
                    if ($img->path) Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }

            // 4) save new images
            $files = $request->file('new_images', []);
            $createdNew = []; // index => ListingImage

            if (!empty($files)) {
                foreach ($files as $file) {
                    $path = $file->store('listings', 'public');

                    $createdNew[] = ListingImage::create([
                        'listing_id' => $listing->id,
                        'path'       => $path,
                        'sort_order' => 9999,
                    ]);
                }
            }

            // 5) max 10 after delete+add
            $total = $listing->images()->count();
            if ($total > 10) {
                throw ValidationException::withMessages([
                    'new_images' => 'Maksimaalselt 10 pilti kokku (olemasolevad + uued).',
                ]);
            }

            // 6) reorder mixed tokens
            if (!empty($mixOrder)) {
                $sort = 0;
                $existingMap = $listing->images()->get()->keyBy('id');

                foreach ($mixOrder as $token) {
                    $token = (string) $token;

                    if (str_starts_with($token, 'e:')) {
                        $id = (int) substr($token, 2);
                        if ($id && $existingMap->has($id)) {
                            $existingMap[$id]->update(['sort_order' => $sort++]);
                        }
                        continue;
                    }

                    if (str_starts_with($token, 'n:')) {
                        $idx = (int) substr($token, 2);
                        if (isset($createdNew[$idx])) {
                            $createdNew[$idx]->update(['sort_order' => $sort++]);
                        }
                        continue;
                    }
                }

                // safety: pane ülejäänud lõppu
                $usedExisting = collect($mixOrder)
                    ->filter(fn ($t) => str_starts_with((string) $t, 'e:'))
                    ->map(fn ($t) => (int) substr((string) $t, 2))
                    ->all();

                foreach ($existingMap as $img) {
                    if (!in_array($img->id, $usedExisting, true)) {
                        $img->update(['sort_order' => $sort++]);
                    }
                }

                $usedNew = collect($mixOrder)
                    ->filter(fn ($t) => str_starts_with((string) $t, 'n:'))
                    ->map(fn ($t) => (int) substr((string) $t, 2))
                    ->all();

                foreach ($createdNew as $i => $img) {
                    if (!in_array($i, $usedNew, true)) {
                        $img->update(['sort_order' => $sort++]);
                    }
                }
            } else {
                // fallback: uued lõppu
                $maxSort = (int) ($listing->images()->max('sort_order') ?? 0);
                foreach ($createdNew as $img) {
                    $img->update(['sort_order' => ++$maxSort]);
                }
            }
        });

        return redirect()
            ->route('listings.mine.show', $listing)
            ->with('status', 'Kuulutus muudetud!');
    }

    public function toggleMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        if ($listing->status === 'archived') {
            $listing->status = 'published';
            $listing->published_at = $listing->published_at ?? now();

            if (!$listing->expires_at || $listing->expires_at->isPast()) {
                $listing->expires_at = now()->addDays(30);
            }

            $listing->save();
            return back()->with('status', 'Kuulutus aktiveeritud!');
        }

        $listing->status = 'archived';
        $listing->save();

        return back()->with('status', 'Kuulutus peatatud (mitteaktiivne).');
    }

    public function publishMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
        if ($listing->status !== 'draft') return back();

        $errors = [];

        if (!$listing->title) $errors['title'] = 'Pealkiri on puudu.';
        if (!$listing->category_id) $errors['category_id'] = 'Kategooria on puudu.';
        if (!$listing->location_id) $errors['location_id'] = 'Asukoht on puudu.';

        if (!empty($errors)) return back()->withErrors($errors);

        $listing->status = 'published';
        $listing->published_at = now();

        if (!$listing->expires_at || $listing->expires_at->isPast()) {
            $listing->expires_at = now()->addDays(30);
        }

        $listing->save();

        return back()->with('status', 'Mustand avaldati (aktiveeriti).');
    }
    public function relistMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        // lubame relist’i siis, kui kuulutus on published ja aegunud
        // (või kui tahad, võid lubada ka archived->published sama nupuga)
        $isExpired = $listing->status === 'published'
            && $listing->expires_at
            && $listing->expires_at->isPast();

        if (!$isExpired) {
            return back();
        }

        $listing->status = 'published';
        $listing->published_at = $listing->published_at ?? now();
        $listing->expires_at = now()->addDays(30);
        $listing->save();

        return back()->with('status', 'Kuulutus pandi uuesti müüki (aktiveeriti).');
    }

    public function destroyMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $images = $listing->images()->get();
        foreach ($images as $img) {
            if ($img->path) Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        $listing->delete();

        return redirect()
            ->route('listings.mine')
            ->with('status', 'Kuulutus kustutatud.');
    }

    public function markSold(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
        $listing->update(['status' => 'sold']);
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

    private function safeJsonArray(?string $raw): array
    {
        if (!$raw) return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * deal => price null
     * free => price 0
     * price => jätab inputi
     */
    private function normalizePriceMode(Request $request): void
    {
        $priceMode = (string) $request->input('price_mode', 'deal');

        if ($priceMode === 'deal') {
            $request->merge(['price' => null]);
        } elseif ($priceMode === 'free') {
            $request->merge(['price' => 0]);
        }
    }
}
