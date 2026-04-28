<?php

namespace App\Services\Messaging;

use App\Models\MessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class MessageAttachmentService
{
    protected ImageManager $imageManager;
    protected string $disk = 'public';

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function store(UploadedFile $file, int $messageId): MessageAttachment
    {
        $mimeType = $file->getMimeType();

        $isImage = is_string($mimeType)
            && str_starts_with($mimeType, 'image/');

        return $isImage
            ? $this->storeImage($file, $messageId)
            : $this->storeFile($file, $messageId);
    }

    protected function storeImage(UploadedFile $file, int $messageId): MessageAttachment
    {
        $storedPaths = [];

        try {
            $image = $this->imageManager->read($file);

            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // LARGE - vestluses avamiseks
            $large = clone $image;
            $large->scaleDown(width: 1600, height: 1600);

            $largePath = $this->generateImagePath('large');

            Storage::disk($this->disk)->put(
                $largePath,
                (string) $large->toJpeg(82)
            );

            $storedPaths[] = $largePath;

            // THUMB - vestluse eelvaate jaoks
            $thumb = clone $image;
            $thumb->scaleDown(width: 500, height: 500);

            $thumbPath = $this->generateImagePath('thumb');

            Storage::disk($this->disk)->put(
                $thumbPath,
                (string) $thumb->toJpeg(75)
            );

            $storedPaths[] = $thumbPath;

            return MessageAttachment::create([
                'message_id' => $messageId,
                'disk' => $this->disk,
                'path' => $largePath,
                'thumb_path' => $thumbPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => 'image/jpeg',
                'size' => Storage::disk($this->disk)->size($largePath),
                'width' => $originalWidth,
                'height' => $originalHeight,
                'type' => 'image',
            ]);
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk($this->disk)->delete($path);
            }

            throw $e;
        }
    }

    protected function storeFile(UploadedFile $file, int $messageId): MessageAttachment
    {
        $path = $file->store('messages/files/' . date('Y/m'), $this->disk);

        return MessageAttachment::create([
            'message_id' => $messageId,
            'disk' => $this->disk,
            'path' => $path,
            'thumb_path' => null,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => null,
            'height' => null,
            'type' => 'file',
        ]);
    }

    protected function generateImagePath(string $type): string
    {
        return 'messages/images/' . $type . '/' . date('Y/m') . '/' . uniqid('', true) . '.jpg';
    }
}