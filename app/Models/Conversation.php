<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'listing_id',
        'seller_id',
        'buyer_id',
        'seller_hidden_at',
        'buyer_hidden_at',
        'fully_hidden_at',
    ];

    protected $casts = [
        'seller_hidden_at' => 'datetime',
        'buyer_hidden_at' => 'datetime',
        'fully_hidden_at' => 'datetime',
    ];

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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function latestTrade(): HasOne
    {
        return $this->hasOne(Trade::class)->latestOfMany();
    }

    public function openTrades(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation']);
    }

    public function latestOpenTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation'])
            ->latestOfMany();
    }

    public function latestReservedTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'reserved')
            ->latestOfMany();
    }

    public function latestAwaitingConfirmationTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'awaiting_confirmation')
            ->latestOfMany();
    }

    public function latestInterestTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'interest')
            ->latestOfMany();
    }

    public function hasParticipant(User $user): bool
    {
        return $this->seller_id === $user->id || $this->buyer_id === $user->id;
    }

    public function isSeller(User $user): bool
    {
        return $this->seller_id === $user->id;
    }

    public function isBuyer(User $user): bool
    {
        return $this->buyer_id === $user->id;
    }

    public function isHiddenFor(User $user): bool
    {
        if ($this->isSeller($user)) {
            return $this->seller_hidden_at !== null;
        }

        if ($this->isBuyer($user)) {
            return $this->buyer_hidden_at !== null;
        }

        return false;
    }

    public function hideFor(User $user): void
    {
        if ($this->isSeller($user)) {
            $this->seller_hidden_at = now();
        } elseif ($this->isBuyer($user)) {
            $this->buyer_hidden_at = now();
        }

        $this->syncFullyHiddenAt();
        $this->save();
    }

    public function unhideFor(User $user): void
    {
        if ($this->isSeller($user)) {
            $this->seller_hidden_at = null;
        } elseif ($this->isBuyer($user)) {
            $this->buyer_hidden_at = null;
        }

        $this->syncFullyHiddenAt();
        $this->save();
    }

    public function unhideForBoth(): void
    {
        $this->seller_hidden_at = null;
        $this->buyer_hidden_at = null;
        $this->fully_hidden_at = null;

        $this->save();
    }

    public function syncFullyHiddenAt(): void
    {
        if ($this->seller_hidden_at && $this->buyer_hidden_at) {
            $this->fully_hidden_at ??= now();

            return;
        }

        $this->fully_hidden_at = null;
    }

    public function otherParticipant(User $user): ?User
    {
        if ($this->isSeller($user)) {
            return $this->buyer;
        }

        if ($this->isBuyer($user)) {
            return $this->seller;
        }

        return null;
    }

    public function readColumnFor(User $user): ?string
    {
        if ($this->isSeller($user)) {
            return 'seller_read_at';
        }

        return 'buyer_read_at';
    }

    public function hasMessagingBlock(User $user): bool
    {
        $otherUser = $this->otherParticipant($user);

        if (! $otherUser) {
            return false;
        }

        return $user->hasMessagingBlockWith($otherUser);
    }

    public function hasOpenTrade(): bool
    {
        return $this->openTrades()->exists();
    }

    public function currentTrade(): ?Trade
    {
        if ($this->relationLoaded('latestOpenTrade') && $this->latestOpenTrade) {
            return $this->latestOpenTrade;
        }

        if ($this->relationLoaded('latestTrade') && $this->latestTrade) {
            return $this->latestTrade;
        }

        return $this->latestOpenTrade()->first() ?? $this->latestTrade()->first();
    }

    public function canUserSendMessages(User $user): bool
    {
        if (! $this->hasParticipant($user)) {
            return false;
        }

        return $this->listing && ! $this->listing->isDeletedStatus();
    }

    public function canUserSeeTradeActions(User $user): bool
    {
        if (! $this->hasParticipant($user)) {
            return false;
        }

        if (! $this->listing || $this->listing->isDeletedStatus()) {
            return false;
        }

        return ! $this->hasMessagingBlock($user);
    }
}
