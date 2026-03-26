<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Rollide konstandid
    |--------------------------------------------------------------------------
    */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_CUSTOMER = 'customer';   // eraisik
    public const ROLE_BUSINESS = 'business';   // ettevõte

    /*
    |--------------------------------------------------------------------------
    | Mass assignable väljad
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        // süsteemne kuvatav nimi
        'name',

        // eraisik
        'first_name',
        'last_name',
        'date_of_birth',

        // kontakt ja asukoht
        'email',
        'phone',
        'location_id',
        'avatar_path',

        // ettevõte
        'company_name',
        'company_reg_no',
        'contact_first_name',
        'contact_last_name',

        // auth & süsteem
        'password',
        'email_verified_at',
        'role',
        'is_active',
        'terms_accepted_at',
        'auth_provider',
        'auth_provider_id',
        'last_login_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Peidetud väljad
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Castid
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Seosed
    |--------------------------------------------------------------------------
    */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')
            ->withTimestamps();
    }

    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Blokeerimised, mille see kasutaja on ise teinud.
     */
    public function blocksInitiated(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    /**
     * Blokeerimised, kus see kasutaja on blokeeritud osapool.
     */
    public function blocksReceived(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessorid / abimeetodid
    |--------------------------------------------------------------------------
    */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? Storage::url($this->avatar_path)
            : null;
    }

    /**
     * Kasutaja initsiaalid (avatarid, menüüd jne).
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

    /*
    |--------------------------------------------------------------------------
    | Rollide kontroll
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Blokeeringute kontroll
    |--------------------------------------------------------------------------
    */
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