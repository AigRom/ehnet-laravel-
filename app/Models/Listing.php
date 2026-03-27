<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Conversation;

class Listing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'location_id',
        'title',
        'description',
        'price',
        'currency',
        'intent',
        'condition',
        'listing_type',   // sale|auction
        'status',         // draft|pending|published|rejected|archived|sold|deleted
        'published_at',
        'expires_at',
        'reviewed_by',
        'reviewed_at',
        'rejected_reason',
        'delivery_options',
    ];

    protected $casts = [
        'user_id'          => 'integer',
        'category_id'      => 'integer',
        'location_id'      => 'integer',
        'reviewed_by'      => 'integer',
        'price'            => 'decimal:2',
        'published_at'     => 'datetime',
        'expires_at'       => 'datetime',
        'reviewed_at'      => 'datetime',
        'delivery_options' => 'array',
        'deleted_at'       => 'datetime',
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
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function auction(): HasOne
    {
        return $this->hasOne(Auction::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
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

    /**
     * Aegunud = published + expires_at on minevikus
     */
    public function isExpired(): bool
    {
        return $this->status === 'published'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Public nähtav = published + published_at olemas + (expires_at puudub või pole aegunud)
     */
    public function isPublicVisible(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && ($this->expires_at === null || $this->expires_at->isFuture() || $this->expires_at->isToday());
    }

    // Helper: Staatuste eestikeelsed nimetused
    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'Mustand',
            'pending'   => 'Ootel',
            'published' => $this->isPublicVisible() ? 'Aktiivne' : 'Aegunud',
            'rejected'  => 'Tagasi lükatud',
            'archived'  => 'Müügist eemaldatud',
            'sold'      => 'Müüdud',
            'deleted'   => 'Kustutatud',
            default     => '—',
        };
    }

    // Helper: Tarne valikud
    public function deliveryOptionsLabels(): array
    {
        $map = [
            'pickup'          => 'Järeletulemine',
            'seller_delivery' => 'Transpordi võimalus',
            'courier'         => 'Saadan kulleriga või pakiautomaati',
            'agreement'       => 'Lepime kokku',
        ];

        $opts = is_array($this->delivery_options) ? $this->delivery_options : [];
        $opts = array_values(array_unique(array_filter($opts)));

        return array_values(array_filter(array_map(
            fn ($v) => $map[$v] ?? $v,
            $opts
        )));
    }

    // Helper: seisukord
    public function conditionLabel(): string
    {
        return match ($this->condition) {
            'new'      => 'Uus',
            'used'     => 'Kasutatud',
            'leftover' => 'Jääk',
            default    => '—',
        };
    }

    /**
     * ÜHINE reegel PUBLIC vaadetele:
     * - status = published
     * - published_at olemas
     * - expires_at NULL või >= now()
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Avalehe feed: public visible + sort
     */
    public function scopeHomeFeed(Builder $query): Builder
    {
        return $query
            ->publicVisible()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}