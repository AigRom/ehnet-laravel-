<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Listing extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'location_id',
        'title',
        'description',
        'price',
        'currency',
        'listing_type',   // sale|auction
        'status',         // draft|pending|published|rejected|archived
        'published_at',
        'reviewed_by',
        'reviewed_at',
        'rejected_reason',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'category_id'  => 'integer',
        'location_id'  => 'integer',
        'reviewed_by'  => 'integer',
        'price'        => 'decimal:2',
        'published_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    // Seosed
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)
            ->orderBy('sort_order');
    }

    public function auction(): HasOne
    {
        return $this->hasOne(Auction::class);
    }

    // Helper: peapilt (esimene sort_order järgi)
    public function coverImage(): ?ListingImage
    {
        return $this->images()->first();
    }

    // Helper: peapildi URL (mugav Blade'is)
    public function coverImageUrl(): ?string
    {
        $img = $this->coverImage();
        return $img ? Storage::url($img->path) : null;
    }
}
