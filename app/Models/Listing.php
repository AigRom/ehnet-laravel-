<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'price'        => 'decimal:2',
        'published_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    // Seosed
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function auction()
    {
        return $this->hasOne(Auction::class);
    }

    // Kasulik “helper”: peapilt
    public function coverImage(): ?ListingImage
    {
        return $this->images()->orderBy('sort_order')->first();
    }
}
