<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\Listing\ListingImageService;

class ListingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $locations = Location::query()
            ->where('is_valid', true)
            ->whereIn('level', [2, 3])
            ->orderBy('full_label_et')
            ->limit(200)
            ->get();

        $submissionToken = (string) \Illuminate\Support\Str::uuid();
        session(['listing_submission_token' => $submissionToken]);

        return view('user.listings.create', compact('categories', 'locations', 'submissionToken'));
    }

    public function store(Request $request, ListingImageService $listingImageService)
    {
        $action = (string) $request->input('action', 'publish');
        $isDraft = $action === 'draft';

        $this->normalizePriceMode($request);

        $rulesDraft = [
            'action' => ['nullable', 'in:publish,draft'],
            'submission_token' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_mode' => ['nullable', 'in:deal,free,price'],
            'condition' => ['nullable', 'in:new,used,leftover'],
            'delivery_options' => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'images_order' => ['nullable', 'string'],
        ];

        $rulesPublish = [
            'action' => ['nullable', 'in:publish,draft'],
            'submission_token' => ['required', 'string'],
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_mode' => ['nullable', 'in:deal,free,price'],
            'condition' => ['nullable', 'in:new,used,leftover'],
            'delivery_options' => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'images_order' => ['nullable', 'string'],
        ];

        $validated = $request->validate($isDraft ? $rulesDraft : $rulesPublish);

        $sessionToken = session('listing_submission_token');
        $formToken = $validated['submission_token'] ?? null;

        if (!$sessionToken || !$formToken || !hash_equals($sessionToken, $formToken)) {
            return redirect()
                ->route('listings.create')
                ->withErrors([
                    'title' => 'Vormi topeltsaatmine blokeeriti. Palun proovi uuesti.',
                ]);
        }

        // Muudame tokeni ühekordseks
        session()->forget('listing_submission_token');

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

        DB::transaction(function () use ($request, $validated, $isDraft, $listingImageService) {
            $delivery = array_values(array_unique($validated['delivery_options'] ?? []));

            $listing = Listing::create([
                'user_id' => $request->user()->id,
                'category_id' => $validated['category_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'price' => array_key_exists('price', $validated) ? $validated['price'] : null,
                'currency' => 'EUR',
                'listing_type' => 'sale',
                'condition' => $validated['condition'] ?? null,
                'delivery_options' => $delivery,
                'status' => $isDraft ? 'draft' : 'published',
                'published_at' => $isDraft ? null : now(),
                'expires_at' => $isDraft ? null : now()->addDays(30),
            ]);

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
                    if (!isset($files[$fileIndex])) {
                        continue;
                    }

                    $listingImageService->store(
                        file: $files[$fileIndex],
                        listingId: $listing->id,
                        sortOrder: $sort++
                    );
                }
            }
        });

        return redirect()
            ->route('listings.mine')
            ->with('success', $isDraft ? 'Mustand on salvestatud.' : 'Kuulutus on avaldatud.');
    }
    public function mine(Request $request)
    {
        $user = $request->user();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hasAnyListings = Listing::query()
            ->where('user_id', $user->id)
            ->exists();

        $listingsQuery = Listing::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted') // Minu kuulutused lehel ei kuvata kustutatud kuulutusi
            ->with([
                'category',
                'location',
                'images',
                'reservedTrade.buyer',
                'reservedTrade.conversation',
                'soldTrade.buyer',
                'soldTrade.conversation',
                'latestActiveTrade.buyer',
                'latestActiveTrade.conversation',
            ]);

        $status = (string) $request->get('status', 'all');

        if ($status === 'active') {
            $listingsQuery
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                });
        } elseif ($status === 'expired') {
            $listingsQuery
                ->where('status', 'published')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now());
        } elseif ($status === 'reserved') {
            $listingsQuery->where('status', 'reserved');
        } elseif ($status === 'sold') {
            $listingsQuery->where('status', 'sold');
        } elseif ($status === 'archived') {
            $listingsQuery->where('status', 'archived');
        } elseif ($status === 'pending') {
            $listingsQuery->where('status', 'pending');
        } elseif ($status === 'draft') {
            $listingsQuery->where('status', 'draft');
            // Kustutatud kuulutuste kuvamine sorteerimis valikus minu kuultuste lehel 
        // } elseif ($status === 'deleted') {
        //     $listingsQuery->where('status', 'deleted');
        } elseif ($status === 'rejected') {
            $listingsQuery->where('status', 'rejected');
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

        $sort = (string) $request->get('sort', 'newest');

        if ($sort === 'oldest') {
            $listingsQuery->orderBy('id');
        } elseif ($sort === 'price_asc') {
            $listingsQuery->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END, price ASC');
        } elseif ($sort === 'price_desc') {
            $listingsQuery->orderByRaw('CASE WHEN price IS NULL THEN 1 ELSE 0 END, price DESC');
        } elseif ($sort === 'expires_soon') {
            $listingsQuery->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END, expires_at ASC');
        } else {
            $listingsQuery->latest('id');
        }

        $listings = $listingsQuery
            ->paginate(24)
            ->withQueryString();

        return view('user.listings.index', [
            'listings' => $listings,
            'categories' => $categories,
            'hasAnyListings' => $hasAnyListings,
            'currentStatus' => $status,
            'currentSort' => $sort,
            'currentQuery' => $q,
            'currentCategoryId' => $categoryId,
        ]);
    }

    public function showMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->load([
            'category',
            'location',
            'images',
            'reservedTrade.buyer',
            'reservedTrade.conversation',
            'soldTrade.buyer',
            'soldTrade.conversation',
            'latestActiveTrade.buyer',
            'latestActiveTrade.conversation',
        ]);

        return view('user.listings.show', compact('listing'));
    }

    public function editMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        if (!$listing->canBeEditedByOwner()) {
            return redirect()
                ->route('listings.mine.show', $listing)
                ->withErrors([
                    'listing' => $this->editBlockedMessage($listing),
                ]);
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $listing->load([
            'category',
            'location',
            'images',
        ]);

        return view('user.listings.edit', compact('listing', 'categories'));
    }

    public function updateMine(Request $request, Listing $listing, ListingImageService $listingImageService)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->refresh();

        if (!$listing->canBeEditedByOwner()) {
            return back()->withErrors([
                'listing' => $this->editBlockedMessage($listing),
            ]);
        }

        $action = (string) $request->input('action', $listing->status === 'draft' ? 'draft' : 'publish');
        $isDraft = $action === 'draft';

        $this->normalizePriceMode($request);

        $rulesDraft = [
            'action' => ['nullable', 'in:publish,draft'],
            'title' => ['nullable', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_mode' => ['nullable', 'in:deal,free,price'],
            'condition' => ['nullable', 'in:new,used,leftover'],
            'delivery_options' => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],
            'new_images' => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'deleted_image_ids' => ['nullable', 'string'],
            'images_order' => ['nullable', 'string'],
        ];

        $rulesPublish = [
            'action' => ['nullable', 'in:publish,draft'],
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_mode' => ['nullable', 'in:deal,free,price'],
            'condition' => ['nullable', 'in:new,used,leftover'],
            'delivery_options' => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => ['in:pickup,seller_delivery,courier,agreement'],
            'new_images' => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'deleted_image_ids' => ['nullable', 'string'],
            'images_order' => ['nullable', 'string'],
        ];

        $validated = $request->validate($isDraft ? $rulesDraft : $rulesPublish);

        if ($isDraft) {
            $hasAny =
                filled($validated['title'] ?? null) ||
                filled($validated['description'] ?? null) ||
                filled($validated['category_id'] ?? null) ||
                filled($validated['location_id'] ?? null) ||
                filled($validated['condition'] ?? null) ||
                !empty($validated['delivery_options'] ?? []) ||
                ($request->filled('price') && $request->input('price') !== null) ||
                !empty($request->file('new_images', []));

            if (!$hasAny) {
                throw ValidationException::withMessages([
                    'title' => 'Täiesti tühja mustandit ei salvestata. Lisa vähemalt pealkiri, kategooria, asukoht, hind, seisukord, kättesaamine või pilt.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $listing, $validated, $listingImageService, $isDraft) {
            $delivery = array_values(array_unique($validated['delivery_options'] ?? []));

            $updateData = [
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'price' => array_key_exists('price', $validated) ? $validated['price'] : null,
                'condition' => $validated['condition'] ?? null,
                'delivery_options' => $delivery,
            ];

            if ($listing->status === 'draft') {
                $updateData['status'] = $isDraft ? 'draft' : 'published';
                $updateData['published_at'] = $isDraft ? null : ($listing->published_at ?? now());
                $updateData['expires_at'] = $isDraft ? null : (($listing->expires_at && $listing->expires_at->isFuture()) ? $listing->expires_at : now()->addDays(30));
            }

            $listing->update($updateData);

            $deletedIds = $this->safeJsonArray($request->input('deleted_image_ids'));
            $deletedIds = array_values(array_filter(array_map('intval', $deletedIds)));

            $mixOrder = $this->safeJsonArray($request->input('images_order'));

            if (!empty($deletedIds)) {
                $toDelete = $listing->images()->whereIn('id', $deletedIds)->get();

                foreach ($toDelete as $img) {
                    $disk = $img->disk ?: 'public';

                    if ($img->path) {
                        Storage::disk($disk)->delete($img->path);
                    }

                    if ($img->thumb_path) {
                        Storage::disk($disk)->delete($img->thumb_path);
                    }

                    $img->delete();
                }
            }

            $files = $request->file('new_images', []);
            $createdNew = [];

            if (!empty($files)) {
                foreach ($files as $file) {
                    $createdNew[] = $listingImageService->store(
                        file: $file,
                        listingId: $listing->id,
                        sortOrder: 9999
                    );
                }
            }

            $total = $listing->images()->count();
            if ($total > 10) {
                throw ValidationException::withMessages([
                    'new_images' => 'Maksimaalselt 10 pilti kokku (olemasolevad + uued).',
                ]);
            }

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
                $maxSort = (int) ($listing->images()->max('sort_order') ?? 0);

                foreach ($createdNew as $img) {
                    $img->update(['sort_order' => ++$maxSort]);
                }
            }
        });

        return redirect()
            ->route('listings.mine.show', $listing)
            ->with('success', $isDraft ? 'Mustand on salvestatud.' : 'Kuulutus on muudetud.');
    }

    public function toggleMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->refresh();

        if (!$listing->canBeToggledByOwner()) {
            return back()->withErrors([
                'listing' => 'Seda kuulutust ei saa peatada ega taasaktiveerida selle praeguses olekus.',
            ]);
        }

        if ($listing->status === 'archived') {
            $listing->status = 'published';
            $listing->published_at = $listing->published_at ?? now();

            if (!$listing->expires_at || $listing->expires_at->isPast()) {
                $listing->expires_at = now()->addDays(30);
            }

            $listing->save();

            return back()->with('success', 'Kuulutus on aktiveeritud.');
        }

        $listing->status = 'archived';
        $listing->save();

        return back()->with('success', 'Kuulutus on peatatud.');
    }

    public function publishMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->refresh();

        if ($listing->status !== 'draft') {
            return back()->withErrors([
                'listing' => 'Avaldada saab ainult mustandit.',
            ]);
        }

        $errors = [];

        if (!$listing->title) {
            $errors['title'] = 'Pealkiri on puudu.';
        }
        if (!$listing->category_id) {
            $errors['category_id'] = 'Kategooria on puudu.';
        }
        if (!$listing->location_id) {
            $errors['location_id'] = 'Asukoht on puudu.';
        }

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        $listing->status = 'published';
        $listing->published_at = now();

        if (!$listing->expires_at || $listing->expires_at->isPast()) {
            $listing->expires_at = now()->addDays(30);
        }

        $listing->save();

        return back()->with('success', 'Mustand on avaldatud.');
    }

    public function relistMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->refresh();

        if (!$listing->isExpired()) {
            return back()->withErrors([
                'listing' => 'Uuesti saab avaldada ainult aegunud kuulutust.',
            ]);
        }

        $listing->status = 'published';
        $listing->published_at = now();
        $listing->expires_at = now()->addDays(30);
        $listing->save();

        return back()->with('success', 'Kuulutus on uuesti müüki pandud.');
    }

    public function destroyMine(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->refresh();

        if (!$listing->canBeDeletedByOwner()) {
            return back()->withErrors([
                'listing' => 'Seda kuulutust ei saa kustutada selle praeguses olekus.',
            ]);
        }

        $listing->load('images');

        $shouldHardDelete = $listing->status === 'draft' || !$listing->hasRelations();

        if ($shouldHardDelete) {
            DB::transaction(function () use ($listing) {
                foreach ($listing->images as $img) {
                    $disk = $img->disk ?: 'public';

                    if ($img->path) {
                        Storage::disk($disk)->delete($img->path);
                    }

                    if ($img->thumb_path) {
                        Storage::disk($disk)->delete($img->thumb_path);
                    }

                    $img->delete();
                }

                $listing->forceDelete();
            });

            return redirect()
                ->route('listings.mine')
                ->with('success', 'Kuulutus on kustutatud.');
        }

        $listing->update([
            'status' => 'deleted',
        ]);

        return redirect()
            ->route('listings.mine')
            ->with('success', 'Kuulutus on kustutatud.');
    }

    public function markSold(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        return back()->withErrors([
            'listing' => 'Kuulutust ei märgita enam otse müüduks. Kasuta vastava ostja vestluses nuppu „Müüdud sellele ostjale”.',
        ]);
    }

    public function markUnsold(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        return back()->withErrors([
            'listing' => 'Müüdud kuulutuse taastamine ei käi enam selle nupu kaudu. Vajadusel lahenda see tehingu loogika või admin-taseme paranduse kaudu.',
        ]);
    }

    public function favorites()
    {
        $user = auth()->user();

        $listings = $user->favorites()
            ->with([
                'images',
                'location',
                'category',
                'user',
            ])
            ->latest('favorites.created_at')
            ->paginate(24);

        return view('user.favorites.index', compact('listings'));
    }

    private function editBlockedMessage(Listing $listing): string
    {
        if ($listing->status === 'deleted') {
            return 'Kustutatud kuulutust ei saa muuta.';
        }

        if ($listing->status === 'reserved') {
            return 'Broneeritud kuulutust ei saa muuta. Lõpeta või vii tehing lõpuni vastava vestluse kaudu.';
        }

        if ($listing->status === 'sold') {
            return 'Müüdud kuulutust ei saa muuta.';
        }

        if ($listing->isExpired()) {
            return 'Aegunud kuulutust ei saa muuta enne uuesti avaldamist.';
        }

        return 'Seda kuulutust ei saa selle praeguses olekus muuta.';
    }

    private function safeJsonArray(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

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