<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_BUSINESS = 'business';

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'date_of_birth',
        'email',
        'phone',
        'location_id',
        'avatar_path',
        'company_name',
        'company_reg_no',
        'contact_first_name',
        'contact_last_name',
        'password',
        'email_verified_at',
        'role',
        'is_active',
        'terms_accepted_at',
        'auth_provider',
        'auth_provider_id',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function soldListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'sold_to_user_id');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')
            ->withTimestamps()
            ->withTrashed();
    }

    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function buyingTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    public function sellingTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'seller_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function blocksInitiated(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function blocksReceived(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_user_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : null;
    }

    public function initials(): string
    {
        return Str::of($this->name ?? '')
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isBusiness(): bool
    {
        return $this->role === self::ROLE_BUSINESS;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasBlocked(User $otherUser): bool
    {
        return $this->blocksInitiated()
            ->where('blocked_user_id', $otherUser->id)
            ->exists();
    }

    public function isBlockedBy(User $otherUser): bool
    {
        return $this->blocksReceived()
            ->where('blocker_id', $otherUser->id)
            ->exists();
    }

    public function hasMessagingBlockWith(User $otherUser): bool
    {
        return $this->hasBlocked($otherUser) || $this->isBlockedBy($otherUser);
    }
}