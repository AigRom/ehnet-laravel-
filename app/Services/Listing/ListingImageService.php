<?php

namespace App\Services\Listing;

use App\Models\ListingImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class ListingImageService
{
    protected ImageManager $imageManager;
    protected string $disk = 'public';

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function store(UploadedFile $file, int $listingId, int $sortOrder): ListingImage
    {
        $storedPaths = [];

        try {
            $image = $this->imageManager->read($file);

            $width = $image->width();
            $height = $image->height();

            $large = clone $image;
            $large->scaleDown(width: 1600);

            $largePath = $this->generatePath('large');

            Storage::disk($this->disk)->put(
                $largePath,
                (string) $large->toJpeg(82)
            );

            $storedPaths[] = $largePath;

            $thumb = clone $image;
            $thumb->scaleDown(width: 500);

            $thumbPath = $this->generatePath('thumb');

            Storage::disk($this->disk)->put(
                $thumbPath,
                (string) $thumb->toJpeg(75)
            );

            $storedPaths[] = $thumbPath;

            return ListingImage::create([
                'listing_id' => $listingId,
                'disk' => $this->disk,
                'path' => $largePath,
                'thumb_path' => $thumbPath,
                'mime_type' => 'image/jpeg',
                'file_size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
                'sort_order' => $sortOrder,
            ]);
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk($this->disk)->delete($path);
            }

            throw $e;
        }
    }

    public function delete(ListingImage $image): void
    {
        $disk = $image->disk ?: $this->disk;

        if ($image->path) {
            Storage::disk($disk)->delete($image->path);
        }

        if ($image->thumb_path) {
            Storage::disk($disk)->delete($image->thumb_path);
        }

        $image->delete();
    }

    protected function generatePath(string $type): string
    {
        return 'listings/' . $type . '/' . date('Y/m') . '/' . uniqid('', true) . '.jpg';
    }
}