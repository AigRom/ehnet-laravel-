<?php

namespace App\Services\Listing;

use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ListingImageSyncService
{
    public function __construct(
        protected ListingImageService $listingImageService,
    ) {}

    /**
     * @param array<int, UploadedFile> $files
     * @param array<int, int> $order
     */
    public function storeForNewListing(Listing $listing, array $files, array $order): void
    {
        if (empty($files)) {
            return;
        }

        if (count($order) !== count($files)) {
            $order = range(0, count($files) - 1);
        }

        $sort = 0;

        foreach ($order as $fileIndex) {
            if (! isset($files[$fileIndex])) {
                continue;
            }

            $this->listingImageService->store(
                file: $files[$fileIndex],
                listingId: $listing->id,
                sortOrder: $sort++
            );
        }
    }

    /**
     * @param array<int, UploadedFile> $newFiles
     * @param array<int, int> $deletedIds
     * @param array<int, mixed> $mixOrder
     */
    public function syncForExistingListing(Listing $listing, array $newFiles, array $deletedIds, array $mixOrder): void
    {
        $deletedIds = array_values(array_unique(array_filter(
            $deletedIds,
            fn ($id) => is_int($id) && $id > 0
        )));

        $existingCountAfterDelete = $listing->images()
            ->when(! empty($deletedIds), fn ($query) => $query->whereNotIn('id', $deletedIds))
            ->count();

        if ($existingCountAfterDelete + count($newFiles) > 10) {
            throw ValidationException::withMessages([
                'new_images' => 'Maksimaalselt 10 pilti kokku (olemasolevad + uued).',
            ]);
        }

        if (! empty($deletedIds)) {
            $toDelete = $listing->images()
                ->whereIn('id', $deletedIds)
                ->get();

            foreach ($toDelete as $image) {
                $this->listingImageService->delete($image);
            }
        }

        $existingMap = $listing->images()->get()->keyBy('id');

        $createdNew = [];

        foreach ($newFiles as $file) {
            $createdNew[] = $this->listingImageService->store(
                file: $file,
                listingId: $listing->id,
                sortOrder: 9999
            );
        }

        if (! empty($mixOrder)) {
            $this->applyMixedOrder(
                existingMap: $existingMap,
                createdNew: $createdNew,
                mixOrder: $mixOrder
            );

            return;
        }

        $maxSort = (int) ($listing->images()->max('sort_order') ?? 0);

        foreach ($createdNew as $image) {
            $image->update([
                'sort_order' => ++$maxSort,
            ]);
        }
    }

    protected function applyMixedOrder($existingMap, array $createdNew, array $mixOrder): void
    {
        $sort = 0;
        $usedExisting = [];
        $usedNew = [];

        foreach ($mixOrder as $token) {
            $token = (string) $token;

            if (str_starts_with($token, 'e:')) {
                $id = (int) substr($token, 2);

                if ($id && $existingMap->has($id)) {
                    $existingMap[$id]->update([
                        'sort_order' => $sort++,
                    ]);

                    $usedExisting[] = $id;
                }

                continue;
            }

            if (str_starts_with($token, 'n:')) {
                $idx = (int) substr($token, 2);

                if (isset($createdNew[$idx])) {
                    $createdNew[$idx]->update([
                        'sort_order' => $sort++,
                    ]);

                    $usedNew[] = $idx;
                }
            }
        }

        foreach ($existingMap as $image) {
            if (! in_array($image->id, $usedExisting, true)) {
                $image->update([
                    'sort_order' => $sort++,
                ]);
            }
        }

        foreach ($createdNew as $idx => $image) {
            if (! in_array($idx, $usedNew, true)) {
                $image->update([
                    'sort_order' => $sort++,
                ]);
            }
        }
    }
}