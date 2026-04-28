<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Location;
use App\Services\Listing\ListingImageService;
use App\Services\Listing\ListingImageSyncService;
use App\Services\Listing\ListingPriceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(): View
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

        $submissionToken = (string) Str::uuid();

        session([
            'listing_submission_token' => $submissionToken,
        ]);

        return view('user.listings.create', compact('categories', 'locations', 'submissionToken'));
    }

    public function store(
        StoreListingRequest $request,
        ListingPriceService $listingPriceService,
        ListingImageSyncService $listingImageSyncService
    ): RedirectResponse {
        $validated = $request->validated();
        $isDraft = $request->isDraft();

        $listing = DB::transaction(function () use ($request, $validated, $isDraft, $listingPriceService, $listingImageSyncService) {
            $pricePayload = $listingPriceService->payloadFor($request->user(), $validated);

            $listing = Listing::create([
                'user_id' => $request->user()->id,
                'category_id' => $validated['category_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'price' => $pricePayload['price'],
                'vat_included' => $pricePayload['vat_included'],
                'currency' => 'EUR',
                'listing_type' => 'sale',
                'condition' => $validated['condition'] ?? null,
                'delivery_options' => $request->deliveryOptions(),
                'status' => $isDraft ? 'draft' : 'published',
                'published_at' => $isDraft ? null : now(),
                'expires_at' => $isDraft ? null : now()->addDays(30),
            ]);

            $files = $request->file('images', []);

            $listingImageSyncService->storeForNewListing(
                listing: $listing,
                files: $files,
                order: $request->normalizedImagesOrder(count($files))
            );

            return $listing;
        });

        session()->forget('listing_submission_token');

        return redirect()
            ->route('listings.mine', ['listing' => $listing->id])
            ->with('success', $isDraft ? 'Mustand on salvestatud.' : 'Kuulutus on avaldatud.');
    }

    public function mine(Request $request): View
    {
        $user = $request->user();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hasAnyListings = Listing::query()
            ->where('user_id', $user->id)
            ->whereNull('owner_hidden_at')
            ->where('status', '!=', 'deleted')
            ->exists();

        $listingsQuery = Listing::query()
            ->where('user_id', $user->id)
            ->whereNull('owner_hidden_at')
            ->where('status', '!=', 'deleted')
            ->with($this->listingRelationsForOwner())
            ->withCount($this->listingCountRelationsForOwner());

        $status = (string) $request->get('status', 'all');

        if ($status === 'active') {
            $listingsQuery
                ->where('status', 'published')
                ->where(function (Builder $query) {
                    $query->whereNull('expires_at')
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
        } elseif ($status === 'rejected') {
            $listingsQuery->where('status', 'rejected');
        }

        $q = trim((string) $request->get('q', ''));

        if ($q !== '') {
            $listingsQuery->where(function (Builder $query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
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

        $activeListing = $this->resolveActiveListing($request, $listings->getCollection()->first());

        return view('user.listings.index', [
            'listings' => $listings,
            'categories' => $categories,
            'hasAnyListings' => $hasAnyListings,
            'currentStatus' => $status,
            'currentSort' => $sort,
            'currentQuery' => $q,
            'currentCategoryId' => $categoryId,
            'activeListing' => $activeListing,
        ]);
    }

    public function showMine(Request $request, Listing $listing): View|RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        if ($listing->owner_hidden_at !== null || $listing->status === 'deleted') {
            return redirect()
                ->route('listings.mine')
                ->withErrors([
                    'listing' => 'See kuulutus on sinu vaatest eemaldatud.',
                ]);
        }

        $listing->load($this->listingRelationsForOwner());
        $listing->loadCount($this->listingCountRelationsForOwner());

        return view('user.listings.show', compact('listing'));
    }

    public function editMine(Request $request, Listing $listing): View|RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        if ($listing->owner_hidden_at !== null) {
            return redirect()
                ->route('listings.mine')
                ->withErrors([
                    'listing' => 'Sinu vaatest eemaldatud kuulutust ei saa muuta.',
                ]);
        }

        if (! $listing->canBeEditedByOwner()) {
            return redirect()
                ->route('listings.mine', ['listing' => $listing->id])
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

    public function updateMine(
        UpdateListingRequest $request,
        Listing $listing,
        ListingPriceService $listingPriceService,
        ListingImageSyncService $listingImageSyncService
    ): RedirectResponse {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        $listing->refresh();

        if ($listing->owner_hidden_at !== null) {
            return redirect()
                ->route('listings.mine')
                ->withErrors([
                    'listing' => 'Sinu vaatest eemaldatud kuulutust ei saa muuta.',
                ]);
        }

        if (! $listing->canBeEditedByOwner()) {
            return back()->withErrors([
                'listing' => $this->editBlockedMessage($listing),
            ]);
        }

        $validated = $request->validated();
        $isDraft = $request->isDraft();

        DB::transaction(function () use ($request, $listing, $validated, $listingPriceService, $listingImageSyncService, $isDraft) {
            $pricePayload = $listingPriceService->payloadFor($request->user(), $validated);

            $updateData = [
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'price' => $pricePayload['price'],
                'vat_included' => $pricePayload['vat_included'],
                'condition' => $validated['condition'] ?? null,
                'delivery_options' => $request->deliveryOptions(),
            ];

            if ($listing->status === 'draft') {
                $updateData['status'] = $isDraft ? 'draft' : 'published';
                $updateData['published_at'] = $isDraft ? null : ($listing->published_at ?? now());
                $updateData['expires_at'] = $isDraft
                    ? null
                    : (($listing->expires_at && $listing->expires_at->isFuture())
                        ? $listing->expires_at
                        : now()->addDays(30));
            }

            $listing->update($updateData);

            $listingImageSyncService->syncForExistingListing(
                listing: $listing,
                newFiles: $request->file('new_images', []),
                deletedIds: $request->deletedImageIds(),
                mixOrder: $request->imagesOrder()
            );
        });

        $returnTo = $this->safeReturnTo(
            (string) $request->input('return_to', ''),
            route('listings.mine', ['listing' => $listing->id])
        );

        return redirect()
            ->to($returnTo)
            ->with('success', $isDraft ? 'Mustand on salvestatud.' : 'Kuulutus on muudetud.');
    }

    public function toggleMine(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        $listing->refresh();

        if (! $listing->canBeToggledByOwner()) {
            return back()->withErrors([
                'listing' => 'Seda kuulutust ei saa peatada ega taasaktiveerida selle praeguses olekus.',
            ]);
        }

        if ($listing->status === 'archived') {
            $listing->status = 'published';
            $listing->published_at = $listing->published_at ?? now();

            if (! $listing->expires_at || $listing->expires_at->isPast()) {
                $listing->expires_at = now()->addDays(30);
            }

            $listing->save();

            return back()->with('success', 'Kuulutus on aktiveeritud.');
        }

        $listing->status = 'archived';
        $listing->save();

        return back()->with('success', 'Kuulutus on peatatud.');
    }

    public function publishMine(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        $listing->refresh();

        if ($listing->owner_hidden_at !== null) {
            return redirect()
                ->route('listings.mine')
                ->withErrors([
                    'listing' => 'Sinu vaatest eemaldatud mustandit ei saa avaldada.',
                ]);
        }

        if ($listing->status !== 'draft') {
            return back()->withErrors([
                'listing' => 'Avaldada saab ainult mustandit.',
            ]);
        }

        $errors = [];

        if (! $listing->title) {
            $errors['title'] = 'Pealkiri on puudu.';
        }

        if (! $listing->category_id) {
            $errors['category_id'] = 'Kategooria on puudu.';
        }

        if (! $listing->location_id) {
            $errors['location_id'] = 'Asukoht on puudu.';
        }

        if (! empty($errors)) {
            return back()->withErrors($errors);
        }

        $listing->status = 'published';
        $listing->published_at = now();

        if (! $listing->expires_at || $listing->expires_at->isPast()) {
            $listing->expires_at = now()->addDays(30);
        }

        $listing->save();

        return back()->with('success', 'Mustand on avaldatud.');
    }

    public function relistMine(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        $listing->refresh();

        if ($listing->owner_hidden_at !== null) {
            return redirect()
                ->route('listings.mine')
                ->withErrors([
                    'listing' => 'Sinu vaatest eemaldatud kuulutust ei saa uuesti avaldada.',
                ]);
        }

        if (! $listing->isExpired()) {
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

    public function destroyMine(
        Request $request,
        Listing $listing,
        ListingImageService $listingImageService
    ): RedirectResponse {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        $listing->refresh();

        if (! $listing->canBeDeletedByOwner()) {
            return back()->withErrors([
                'listing' => 'Seda kuulutust ei saa praeguses olekus sinu vaatest eemaldada.',
            ]);
        }

        $listing->load('images');

        if ($listing->status === 'draft') {
            DB::transaction(function () use ($listing, $listingImageService) {
                foreach ($listing->images as $image) {
                    $listingImageService->delete($image);
                }

                $listing->delete();
            });

            return redirect()
                ->route('listings.mine')
                ->with('success', 'Mustand on kustutatud.');
        }

        $updateData = [
            'owner_hidden_at' => now(),
        ];

        if ($listing->status === 'published') {
            $updateData['status'] = 'archived';
        }

        $listing->update($updateData);

        return redirect()
            ->route('listings.mine')
            ->with('success', 'Kuulutus eemaldati sinu vaatest.');
    }

    public function markSold(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        return back()->withErrors([
            'listing' => 'Kuulutust ei märgita enam otse müüduks. Kasuta vastava ostja vestluses nuppu „Müüdud sellele ostjale”.',
        ]);
    }

    public function markUnsold(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless((int) $listing->user_id === (int) $request->user()->id, 403);

        return back()->withErrors([
            'listing' => 'Müüdud kuulutuse taastamine ei käi enam selle nupu kaudu. Vajadusel lahenda see tehingu loogika või admin-taseme paranduse kaudu.',
        ]);
    }

    public function favorites(): View
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

    private function resolveActiveListing(Request $request, ?Listing $fallback): ?Listing
    {
        $activeListingId = $request->integer('listing');

        if ($activeListingId) {
            $activeListing = Listing::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('owner_hidden_at')
                ->where('status', '!=', 'deleted')
                ->with($this->listingRelationsForOwner())
                ->withCount($this->listingCountRelationsForOwner())
                ->find($activeListingId);

            if ($activeListing) {
                return $activeListing;
            }
        }

        if ($fallback) {
            $fallback->loadMissing($this->listingRelationsForOwner());
            $fallback->loadCount($this->listingCountRelationsForOwner());
        }

        return $fallback;
    }

    private function listingRelationsForOwner(): array
    {
        return [
            'category',
            'location',
            'images',

            'reservedTrade.buyer',
            'reservedTrade.conversation',

            'awaitingConfirmationTrade.buyer',
            'awaitingConfirmationTrade.conversation',

            'soldTrade.buyer',
            'soldTrade.conversation',

            'latestActiveTrade.buyer',
            'latestActiveTrade.conversation',

            'purchaseRequests.buyer',
            'purchaseRequests.conversation',

            'interestTrades.buyer',
            'interestTrades.conversation',
        ];
    }

    private function listingCountRelationsForOwner(): array
    {
        return [
            'interestTrades as purchase_requests_count',
        ];
    }

    private function safeReturnTo(string $returnTo, string $fallback): string
    {
        $returnTo = trim($returnTo);

        if ($returnTo === '') {
            return $fallback;
        }

        if (str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//')) {
            return $returnTo;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '' && ($returnTo === $appUrl || str_starts_with($returnTo, $appUrl . '/'))) {
            return $returnTo;
        }

        return $fallback;
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

        if ($listing->owner_hidden_at !== null) {
            return 'Sinu vaatest eemaldatud kuulutust ei saa muuta.';
        }

        return 'Seda kuulutust ei saa selle praeguses olekus muuta.';
    }
}