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
        'sort_order',
    ];

    protected $casts = [
        'listing_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Kuulutus, millele pilt kuulub
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Täielik URL pildile (storage / CDN)
     * Kasutamiseks Blade'is: {{ $image->url() }}
     */
    public function url(): string
    {
        return Storage::url($this->path);
    }
}
