<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    /**
     * Mass assignment jaoks lubatud väljad.
     */
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

    /**
     * Sõnum, mille juurde manus kuulub.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Kas manus on pilt.
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Kas manus on tavaline fail, mitte pilt.
     */
    public function isFile(): bool
    {
        return ! $this->isImage();
    }

    /**
     * Tagastab manuse avaliku URL-i.
     */
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Tagastab thumbnail URL-i.
     * Kui thumbnail puudub, kasutab suuremat pilti.
     */
    public function thumbUrl(): string
    {
        return Storage::disk($this->disk)->url($this->thumb_path ?: $this->path);
    }

    /**
     * Tagastab manuse suuruse kilobaitides vormindatud kujul.
     *
     * Näide: 12,5
     */
    public function sizeKb(): string
    {
        return number_format(($this->size ?? 0) / 1024, 1, ',', ' ');
    }
}