<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'disk',
        'path',
        'thumb_path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'type',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isFile(): bool
    {
        return ! $this->isImage();
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function thumbUrl(): string
    {
        return Storage::disk($this->disk)->url($this->thumb_path ?: $this->path);
    }

    public function sizeKb(): string
    {
        return number_format(($this->size ?? 0) / 1024, 1, ',', ' ');
    }
}
