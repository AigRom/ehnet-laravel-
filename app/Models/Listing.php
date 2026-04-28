<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'intent',
        'condition',
        'listing_type',
        'status',
        'sold_to_user_id',
        'sold_trade_id',
        'published_at',
        'expires_at',
        'reviewed_by',
        'reviewed_at',
        'rejected_reason',
        'delivery_options',
        'vat_included',
        'owner_hidden_at',
    ];

    protected $casts = [
        'user_id'          => 'integer',
        'category_id'      => 'integer',
        'location_id'      => 'integer',
        'reviewed_by'      => 'integer',
        'sold_to_user_id'  => 'integer',
        'sold_trade_id'    => 'integer',
        'price'            => 'decimal:2',
        'published_at'     => 'datetime',
        'expires_at'       => 'datetime',
        'reviewed_at'      => 'datetime',
        'delivery_options' => 'array',
        'vat_included'     => 'boolean',
        'owner_hidden_at'  => 'datetime',
    ];

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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function activeTrades(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation'])
            ->latest('id');
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation'])
            ->latest('id');
    }

    public function interestTrades(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->where('status', 'interest')
            ->latest('id');
    }

    public function reservedTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'reserved')
            ->latestOfMany();
    }

    public function awaitingConfirmationTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'awaiting_confirmation')
            ->latestOfMany();
    }

    public function latestActiveTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation'])
            ->latestOfMany();
    }

    public function soldToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }

    public function soldTrade(): BelongsTo
    {
        return $this->belongsTo(Trade::class, 'sold_trade_id');
    }

    public function coverImage(): ?ListingImage
    {
        if ($this->relationLoaded('images')) {
            return $this->images
                ->sortBy('sort_order')
                ->first();
        }

        return $this->images()->first();
    }

    public function coverImageUrl(): ?string
    {
        return $this->coverImage()?->url();
    }

    public function coverThumbUrl(): ?string
    {
        return $this->coverImage()?->thumbUrl();
    }

    public function coverThumbUrlOrPlaceholder(): string
    {
        return $this->coverThumbUrl() ?? asset('images/placeholder.png');
    }

    public function detailImageItems(): array
    {
        if (! $this->relationLoaded('images')) {
            $this->load('images');
        }

        return $this->images
            ->map(fn ($img) => [
                'full' => $img->url(),
                'thumb' => $img->thumbUrl(),
            ])
            ->filter(fn ($item) => ! empty($item['full']))
            ->values()
            ->all();
    }

    public function hasRelations(): bool
    {
        return $this->favoritedBy()->exists()
            || $this->conversations()->exists()
            || $this->trades()->exists();
    }

    public function isExpired(): bool
    {
        return $this->status === 'published'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function isDeletedStatus(): bool
    {
        return $this->status === 'deleted';
    }

    public function isHiddenForOwner(): bool
    {
        return $this->owner_hidden_at !== null;
    }

    public function isPublicVisible(): bool
    {
        if ($this->owner_hidden_at !== null) {
            return false;
        }

        if ($this->status === 'reserved') {
            return $this->published_at !== null;
        }

        return $this->status === 'published'
            && $this->published_at !== null
            && (
                $this->expires_at === null
                || $this->expires_at->isFuture()
                || $this->expires_at->isToday()
            );
    }

    public function isActivePublished(): bool
    {
        return $this->status === 'published' && $this->isPublicVisible();
    }

    public function canAcceptTradeInterest(): bool
    {
        return $this->isActivePublished();
    }

    public function canAcceptTradeReservation(): bool
    {
        return $this->isActivePublished();
    }

    public function hasActiveTrade(): bool
    {
        if ($this->relationLoaded('latestActiveTrade')) {
            return $this->latestActiveTrade !== null;
        }

        return $this->activeTrades()->exists();
    }

    public function hasReservedTrade(): bool
    {
        if ($this->relationLoaded('reservedTrade')) {
            return $this->reservedTrade !== null;
        }

        return $this->reservedTrade()->exists();
    }

    public function hasAwaitingConfirmationTrade(): bool
    {
        if ($this->relationLoaded('awaitingConfirmationTrade')) {
            return $this->awaitingConfirmationTrade !== null;
        }

        return $this->awaitingConfirmationTrade()->exists();
    }

    public function purchaseRequestsCount(): int
    {
        if (array_key_exists('purchase_requests_count', $this->attributes)) {
            return (int) $this->attributes['purchase_requests_count'];
        }

        if ($this->relationLoaded('interestTrades')) {
            return $this->interestTrades->count();
        }

        return $this->interestTrades()->count();
    }

    public function hasPurchaseRequests(): bool
    {
        return $this->purchaseRequestsCount() > 0;
    }

    public function purchaseRequestsLabel(): ?string
    {
        $count = $this->purchaseRequestsCount();

        if ($count < 1) {
            return null;
        }

        return trans_choice('Ostusoov: :count|Ostusoove: :count', $count, [
            'count' => $count,
        ]);
    }

    public function singleInterestTrade(): ?Trade
    {
        if ($this->relationLoaded('interestTrades')) {
            return $this->interestTrades->count() === 1
                ? $this->interestTrades->first()
                : null;
        }

        $trades = $this->interestTrades()
            ->with(['buyer', 'conversation'])
            ->limit(2)
            ->get();

        return $trades->count() === 1
            ? $trades->first()
            : null;
    }

    public function canBeEditedByOwner(): bool
    {
        if ($this->owner_hidden_at !== null) {
            return false;
        }

        if (in_array($this->status, ['deleted', 'reserved', 'sold'], true)) {
            return false;
        }

        return in_array($this->status, ['draft', 'pending', 'published', 'archived', 'rejected'], true);
    }

    public function canBeDeletedByOwner(): bool
    {
        if ($this->owner_hidden_at !== null) {
            return false;
        }

        if ($this->status === 'reserved') {
            return false;
        }

        if ($this->status === 'deleted') {
            return false;
        }

        return true;
    }

    public function canBeToggledByOwner(): bool
    {
        if ($this->owner_hidden_at !== null) {
            return false;
        }

        if (! in_array($this->status, ['published', 'archived'], true)) {
            return false;
        }

        return ! $this->isExpired();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'Mustand',
            'pending'   => 'Ootel',
            'published' => $this->isPublicVisible() ? 'Aktiivne' : 'Aegunud',
            'reserved'  => 'Broneeritud',
            'rejected'  => 'Tagasi lükatud',
            'archived'  => 'Müügist eemaldatud',
            'sold'      => 'Müüdud',
            'deleted'   => 'Kustutatud',
            default     => '—',
        };
    }

    public function tradeBuyerName(): ?string
    {
        return $this->awaitingConfirmationTrade?->buyerName()
            ?? $this->reservedTrade?->buyerName()
            ?? $this->soldTrade?->buyerName()
            ?? $this->soldToUser?->name
            ?? null;
    }

    public function canShowTradeBuyerTo(?User $viewer): bool
    {
        return $viewer !== null
            && (int) $viewer->id === (int) $this->user_id;
    }

    public function isSoldToUser(?User $viewer): bool
    {
        if (! $viewer) {
            return false;
        }

        if ($this->sold_to_user_id) {
            return (int) $this->sold_to_user_id === (int) $viewer->id;
        }

        return $this->soldTrade
            && (int) $this->soldTrade->buyer_id === (int) $viewer->id;
    }

    public function isReservedForUser(?User $user): bool
    {
        if (! $user || ! $this->isReserved()) {
            return false;
        }

        $trade = $this->awaitingConfirmationTrade
            ?? $this->reservedTrade
            ?? null;

        if (! $trade) {
            return false;
        }

        return (int) $trade->buyer_id === (int) $user->id;
    }

    public function preferredConversation(): ?Conversation
    {
        if ($this->awaitingConfirmationTrade?->conversation) {
            return $this->awaitingConfirmationTrade->conversation;
        }

        if ($this->reservedTrade?->conversation) {
            return $this->reservedTrade->conversation;
        }

        if ($this->soldTrade?->conversation) {
            return $this->soldTrade->conversation;
        }

        $singleInterestTrade = $this->singleInterestTrade();

        return $singleInterestTrade?->conversation;
    }

    public function conversationUrl(): ?string
    {
        $conversation = $this->preferredConversation();

        return $conversation ? route('messages.show', $conversation) : null;
    }

    public function statusHelpText(?User $viewer = null): ?string
    {
        $viewer ??= auth()->user();

        $canShowBuyerName = $this->canShowTradeBuyerTo($viewer);
        $buyerName = $this->tradeBuyerName();

        if ($this->status === 'reserved' && $this->hasAwaitingConfirmationTrade()) {
            if ($canShowBuyerName && $buyerName !== null) {
                return __('Ootab kinnitust: :name', ['name' => $buyerName]);
            }

            if ($this->isReservedForUser($viewer)) {
                return __('Ootab sinu kinnitust');
            }

            return null;
        }

        if ($this->status === 'reserved') {
            if ($canShowBuyerName && $buyerName !== null) {
                return __('Broneeritud: :name', ['name' => $buyerName]);
            }

            if ($this->isReservedForUser($viewer)) {
                return __('Broneeritud sulle');
            }

            return null;
        }

        if ($this->status === 'sold') {
            if ($canShowBuyerName && $buyerName !== null) {
                return __('Müüdud: :name', ['name' => $buyerName]);
            }

            if ($this->isSoldToUser($viewer)) {
                return __('Müüdud sulle');
            }

            return null;
        }

        if ($this->status === 'published' && $canShowBuyerName) {
            $count = $this->purchaseRequestsCount();

            if ($count > 0) {
                return __('Ostusoove: :count', [
                    'count' => $count,
                ]);
            }
        }

        if ($this->isExpired() && $this->expires_at !== null) {
            return __('Aegus: :date', [
                'date' => $this->expires_at->format('d.m.Y'),
            ]);
        }

        if ($this->status === 'deleted') {
            return __('Kuulutus on kustutatud');
        }

        return null;
    }

    public function showExpiryDate(): bool
    {
        return $this->status === 'published' && $this->expires_at !== null;
    }

    public function expiryLabel(): ?string
    {
        if (! $this->showExpiryDate()) {
            return null;
        }

        return $this->isExpired() ? __('Aegus:') : __('Aegub:');
    }

    public function expiryDateText(): ?string
    {
        if (! $this->showExpiryDate()) {
            return null;
        }

        return $this->expires_at?->format('d.m.Y');
    }

    public function priceLabel(): string
    {
        if ($this->price === null) {
            return __('Kokkuleppel');
        }

        if ((float) $this->price === 0.0) {
            return __('Tasuta');
        }

        $price = (float) $this->price;

        $formatted = floor($price) == $price
            ? number_format($price, 0, '.', '')
            : number_format($price, 2, '.', '');

        return $formatted . ' €';
    }

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

    public function conditionLabel(): string
    {
        return match ($this->condition) {
            'new'      => 'Uus',
            'used'     => 'Kasutatud',
            'leftover' => 'Jääk',
            default    => '—',
        };
    }

    public function canBuyerSendMessage(?User $viewer = null): bool
    {
        if ($this->isActivePublished()) {
            return true;
        }

        if ($this->isReserved()) {
            return $this->isReservedForUser($viewer);
        }

        return false;
    }

    public function canBuyerExpressBuyIntent(): bool
    {
        return $this->isActivePublished();
    }

    public function scopePublicVisible(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->whereNull('owner_hidden_at')
            ->where(function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('status', 'published')
                        ->where(function (Builder $qq) {
                            $qq->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        });
                })->orWhere('status', 'reserved');
            });
    }

    public function scopeHomeFeed(Builder $query): Builder
    {
        return $query
            ->publicVisible()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}