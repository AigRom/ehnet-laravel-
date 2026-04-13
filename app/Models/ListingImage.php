<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    protected $table = 'listing_images';

    protected $fillable = [
        'listing_id',
        'path',
        'thumb_path',
        'disk',
        'mime_type',
        'file_size',
        'width',
        'height',
        'sort_order',
    ];

    protected $casts = [
        'listing_id' => 'integer',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }

    public function thumbUrl(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->thumb_path ?: $this->path);
    }
}