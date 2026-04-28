<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    public const STATUS_INTEREST = 'interest';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_AWAITING_CONFIRMATION = 'awaiting_confirmation';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_INTEREST,
        self::STATUS_RESERVED,
        self::STATUS_AWAITING_CONFIRMATION,
    ];

    public const FINAL_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'conversation_id',
        'listing_id',
        'seller_id',
        'buyer_id',
        'status',
        'contact_revealed_at',
        'reserved_at',
        'awaiting_confirmation_at',
        'completed_at',
        'buyer_confirmed_received_at',
        'cancelled_at',
        'buyer_hidden_at',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'listing_id' => 'integer',
        'seller_id' => 'integer',
        'buyer_id' => 'integer',
        'contact_revealed_at' => 'datetime',
        'reserved_at' => 'datetime',
        'awaiting_confirmation_at' => 'datetime',
        'completed_at' => 'datetime',
        'buyer_confirmed_received_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'buyer_hidden_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isInterest(): bool
    {
        return $this->status === self::STATUS_INTEREST;
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isAwaitingConfirmation(): bool
    {
        return $this->status === self::STATUS_AWAITING_CONFIRMATION;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isBuyerConfirmed(): bool
    {
        return $this->buyer_confirmed_received_at !== null;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }

    public function canBeReserved(): bool
    {
        return $this->isInterest();
    }

    public function canBeMarkedAsHandedOver(): bool
    {
        return $this->isReserved();
    }

    public function canBeCompleted(): bool
    {
        return $this->canBeMarkedAsHandedOver();
    }

    public function canBeCancelled(): bool
    {
        return $this->isActive();
    }

    public function canBeConfirmedByBuyer(): bool
    {
        return $this->isAwaitingConfirmation()
            && ! $this->isBuyerConfirmed();
    }

    public function contactsRevealed(): bool
    {
        return $this->contact_revealed_at !== null;
    }

    public function buyerName(): ?string
    {
        return $this->buyer?->name;
    }

    public function sellerName(): ?string
    {
        return $this->seller?->name;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_INTEREST => 'Ostusoov',
            self::STATUS_RESERVED => 'Broneeritud',
            self::STATUS_AWAITING_CONFIRMATION => 'Ootab kinnitust',
            self::STATUS_COMPLETED => 'Lõpetatud',
            self::STATUS_CANCELLED => 'Katkestatud',
            default => '—',
        };
    }

    public function isAwaitingBuyerConfirmation(): bool
    {
        return $this->isAwaitingConfirmation()
            && ! $this->isBuyerConfirmed();
    }

    public function involvesUser(User $user): bool
    {
        return in_array((int) $user->id, [
            (int) $this->buyer_id,
            (int) $this->seller_id,
        ], true);
    }

    public function canBeReviewedBy(User $user): bool
    {
        return $this->isCompleted()
            && $this->isBuyerConfirmed()
            && $this->involvesUser($user);
    }

    public function reviewTargetFor(User $user): ?User
    {
        if ((int) $user->id === (int) $this->buyer_id) {
            return $this->seller;
        }

        if ((int) $user->id === (int) $this->seller_id) {
            return $this->buyer;
        }

        return null;
    }

    public function hasReviewFrom(User $user): bool
    {
        return $this->reviews()
            ->where('reviewer_id', $user->id)
            ->exists();
    }

    public function isHiddenForBuyer(): bool
    {
        return $this->buyer_hidden_at !== null;
    }

    public function canBeHiddenByBuyer(): bool
    {
        if ($this->isHiddenForBuyer()) {
            return false;
        }

        return $this->isFinal();
    }
}