<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /*
    |--------------------------------------------------------------------------
    | Rollide konstandid
    |--------------------------------------------------------------------------
    */
    public const ROLE_ADMIN     = 'admin';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_CUSTOMER  = 'customer'; // eraisik
    public const ROLE_BUSINESS  = 'business'; // ettevõte

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
        'region',
        'city',

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
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'last_login_at'     => 'datetime',
            'date_of_birth'     => 'date',

            'is_active'         => 'boolean',

            'password'          => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Abimeetodid
    |--------------------------------------------------------------------------
    */

    /**
     * Kasutaja initsiaalid (avatarid, menüüd jne)
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
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
}
