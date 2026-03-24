<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /**
     * Mass assignment lubatud väljad
     */
    protected $fillable = [
        'listing_id',
        'seller_id',
        'buyer_id',
        'seller_hidden_at',
        'buyer_hidden_at',
        'fully_hidden_at',
    ];

    /**
     * Castime hidden väljad Carbon kuupäevadeks
     */
    protected $casts = [
        'seller_hidden_at' => 'datetime',
        'buyer_hidden_at' => 'datetime',
        'fully_hidden_at' => 'datetime',
    ];

    /**
     * Kuulutus, mille kohta vestlus käib
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Müüja (kuulutuse omanik)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Ostja (vestluse algataja)
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Kõik vestluse sõnumid (vanimast uuemani)
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Viimane sõnum (kiiremaks kuvamiseks listis)
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Kontrollib, kas kasutaja kuulub sellesse vestlusesse
     */
    public function hasParticipant(User $user): bool
    {
        return $this->seller_id === $user->id || $this->buyer_id === $user->id;
    }

    /**
     * Kas kasutaja on müüja
     */
    public function isSeller(User $user): bool
    {
        return $this->seller_id === $user->id;
    }

    /**
     * Kas kasutaja on ostja
     */
    public function isBuyer(User $user): bool
    {
        return $this->buyer_id === $user->id;
    }

    /**
     * Kas vestlus on sellele kasutajale peidetud
     */
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

    /**
     * Peidab vestluse konkreetse kasutaja jaoks
     *
     * - täidab vastava hidden välja
     * - kontrollib, kas mõlemad pooled on peitnud
     * - uuendab fully_hidden_at
     */
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

    /**
     * Taastab vestluse nähtavuse konkreetse kasutaja jaoks
     *
     * - nullib vastava hidden välja
     * - kui mõlemad pooled ei ole enam peitnud, nullib fully_hidden_at
     */
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

    /**
     * Taastab vestluse nähtavuse mõlemale poolele
     *
     * Kasutatakse näiteks:
     * - uue sõnumi saatmisel
     */
    public function unhideForBoth(): void
    {
        $this->seller_hidden_at = null;
        $this->buyer_hidden_at = null;
        $this->fully_hidden_at = null;

        $this->save();
    }

    /**
     * Sünkroniseerib fully_hidden_at välja
     *
     * - kui mõlemad pooled on peitnud → määrab fully_hidden_at (kui pole juba määratud)
     * - kui vähemalt üks pool on nähtav → nullib fully_hidden_at
     */
    public function syncFullyHiddenAt(): void
    {
        if ($this->seller_hidden_at && $this->buyer_hidden_at) {
            // Määrame ainult siis, kui see pole juba olemas
            $this->fully_hidden_at ??= now();
            return;
        }

        // Kui vähemalt üks pool on nähtav, ei ole vestlus enam "fully hidden"
        $this->fully_hidden_at = null;
    }

    /**
     * Tagastab vestluse teise osapoole antud kasutaja suhtes
     */
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

    /**
     * Kas selle vestluse kahe osapoole vahel on sõnumiblokk
     */
    public function hasMessagingBlock(User $user): bool
    {
        $otherUser = $this->otherParticipant($user);

        if (!$otherUser) {
            return false;
        }

        return $user->hasMessagingBlockWith($otherUser);
    }
}