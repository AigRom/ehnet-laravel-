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

    /**
     * Mass assignment jaoks lubatud väljad.
     */
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

    /**
     * Väljad, mida ei kuvata serialiseerimisel.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tüübimuundused.
     */
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

    /**
     * Kasutaja asukoht.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Kasutaja loodud kuulutused.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Kuulutused, mis on sellele kasutajale müüdud.
     */
    public function soldListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'sold_to_user_id');
    }

    /**
     * Kasutaja lemmikuks märgitud kuulutused.
     */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')
            ->withTimestamps();
    }

    /**
     * Vestlused, kus kasutaja on ostja.
     */
    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    /**
     * Vestlused, kus kasutaja on müüja.
     */
    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    /**
     * Tehingud, kus kasutaja on ostja.
     */
    public function buyingTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    /**
     * Tehingud, kus kasutaja on müüja.
     */
    public function sellingTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'seller_id');
    }

    /**
     * Kasutaja saadetud sõnumid.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Blokeeringud, mille kasutaja ise on algatanud.
     */
    public function blocksInitiated(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    /**
     * Blokeeringud, kus kasutaja on blokeeritud osapool.
     */
    public function blocksReceived(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_user_id');
    }

    /**
     * Kasutajale antud tagasisided.
     *
     * Need on review'd, kus see kasutaja on hinnangu saaja.
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    /**
     * Kasutaja antud tagasisided.
     *
     * Need on review'd, kus see kasutaja on hinnangu andja.
     */
    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Kasutaja avatari avalik URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : null;
    }

    /**
     * Kasutaja initsiaalid avatar fallbacki jaoks.
     */
    public function initials(): string
    {
        return Str::of($this->name ?? '')
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Kas kasutaja on administraator.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Kas kasutaja on moderaator.
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Kas kasutaja on tavakasutaja.
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Kas kasutaja on ettevõtte konto.
     */
    public function isBusiness(): bool
    {
        return $this->role === self::ROLE_BUSINESS;
    }

    /**
     * Kas kasutajakonto on aktiivne.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Kas kasutaja on teise kasutaja blokeerinud.
     */
    public function hasBlocked(User $otherUser): bool
    {
        return $this->blocksInitiated()
            ->where('blocked_user_id', $otherUser->id)
            ->exists();
    }

    /**
     * Kas teine kasutaja on selle kasutaja blokeerinud.
     */
    public function isBlockedBy(User $otherUser): bool
    {
        return $this->blocksReceived()
            ->where('blocker_id', $otherUser->id)
            ->exists();
    }

    /**
     * Kas kasutajate vahel on sõnumivahetuse blokk kummas tahes suunas.
     */
    public function hasMessagingBlockWith(User $otherUser): bool
    {
        return $this->hasBlocked($otherUser) || $this->isBlockedBy($otherUser);
    }

    /**
     * Kasutaja keskmine saadud hinnang 1–5 skaalal.
     *
     * Kui hinnanguid pole, tagastab 0.0.
     */
    public function averageRating(): float
    {
        return round((float) ($this->reviewsReceived()->avg('rating') ?? 0), 1);
    }

    /**
     * Kasutajale jäetud hinnangute koguarv.
     */
    public function reviewsCount(): int
    {
        return $this->reviewsReceived()->count();
    }

    /**
     * Kas kasutajal on vähemalt üks saadud hinnang.
     */
    public function hasReviews(): bool
    {
        return $this->reviewsCount() > 0;
    }
}