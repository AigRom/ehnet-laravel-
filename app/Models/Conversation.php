<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /**
     * Mass assignment lubatud väljad.
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
     * Castime kuupäevaväljad Carbon kuupäevadeks.
     */
    protected $casts = [
        'seller_hidden_at' => 'datetime',
        'buyer_hidden_at' => 'datetime',
        'fully_hidden_at' => 'datetime',
    ];

    /**
     * Kuulutus, mille kohta vestlus käib.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class)->withTrashed();
    }

    /**
     * Müüja ehk kuulutuse omanik.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Ostja ehk vestluse algataja.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Kõik vestluse sõnumid vanimast uuemani.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Viimane sõnum vestluses.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Kõik selle vestluse tehingukatsed.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    /**
     * Viimane tehingukatse sõltumata staatusest.
     */
    public function latestTrade(): HasOne
    {
        return $this->hasOne(Trade::class)->latestOfMany();
    }

    /**
     * Kõik aktiivsed tehingud selles vestluses.
     *
     * Aktiivseks loeme interest ja reserved staatusega tehingud.
     */
    public function openTrades(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->whereIn('status', ['interest', 'reserved']);
    }

    /**
     * Viimane aktiivne tehing selles vestluses.
     */
    public function latestOpenTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->whereIn('status', ['interest', 'reserved'])
            ->latestOfMany();
    }

    /**
     * Viimane reserveeritud tehing selles vestluses.
     */
    public function latestReservedTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'reserved')
            ->latestOfMany();
    }

    /**
     * Viimane ostusoovi staatuses tehing selles vestluses.
     */
    public function latestInterestTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'interest')
            ->latestOfMany();
    }

    /**
     * Kontrollib, kas kasutaja kuulub sellesse vestlusesse.
     */
    public function hasParticipant(User $user): bool
    {
        return $this->seller_id === $user->id || $this->buyer_id === $user->id;
    }

    /**
     * Kas kasutaja on müüja.
     */
    public function isSeller(User $user): bool
    {
        return $this->seller_id === $user->id;
    }

    /**
     * Kas kasutaja on ostja.
     */
    public function isBuyer(User $user): bool
    {
        return $this->buyer_id === $user->id;
    }

    /**
     * Kas vestlus on sellele kasutajale peidetud.
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
     * Peidab vestluse konkreetse kasutaja jaoks.
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
     * Taastab vestluse nähtavuse konkreetse kasutaja jaoks.
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
     * Taastab vestluse nähtavuse mõlemale poolele.
     */
    public function unhideForBoth(): void
    {
        $this->seller_hidden_at = null;
        $this->buyer_hidden_at = null;
        $this->fully_hidden_at = null;

        $this->save();
    }

    /**
     * Sünkroniseerib fully_hidden_at välja.
     *
     * Kui mõlemad pooled on vestluse peitnud, märgime selle täielikult peidetuks.
     */
    public function syncFullyHiddenAt(): void
    {
        if ($this->seller_hidden_at && $this->buyer_hidden_at) {
            $this->fully_hidden_at ??= now();
            return;
        }

        $this->fully_hidden_at = null;
    }

    /**
     * Tagastab vestluse teise osapoole antud kasutaja suhtes.
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
     * Kas selle vestluse kahe osapoole vahel on sõnumiblokk.
     */
    public function hasMessagingBlock(User $user): bool
    {
        $otherUser = $this->otherParticipant($user);

        if (!$otherUser) {
            return false;
        }

        return $user->hasMessagingBlockWith($otherUser);
    }

    /**
     * Kas vestluses on aktiivne tehing.
     *
     * Aktiivseks loeme interest või reserved staatusega tehingu.
     */
    public function hasOpenTrade(): bool
    {
        return $this->openTrades()->exists();
    }

    /**
     * Tagastab vestluse hetkel kõige olulisema tehingu.
     *
     * Eelistus:
     * 1. viimane aktiivne tehing
     * 2. viimane tehing üldse
     */
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

    /**
     * Kas antud kasutaja võib selles vestluses uusi sõnumeid saata.
     *
     * Sõnumite saatmine on suletud, kui kuulutus puudub või on kustutatud.
     */
    public function canUserSendMessages(User $user): bool
    {
        if (!$this->hasParticipant($user)) {
            return false;
        }

        return $this->listing && !$this->listing->isDeletedStatus();
    }

    /**
     * Kas antud kasutajale võib näidata tehinguga seotud tegevusnuppe.
     *
     * Tehingutegevused peavad olema peidetud, kui:
     * - kasutaja ei kuulu vestlusesse
     * - kuulutus puudub
     * - kuulutus on kustutatud
     * - osapoolte vahel on sõnumiblokk
     */
    public function canUserSeeTradeActions(User $user): bool
    {
        if (!$this->hasParticipant($user)) {
            return false;
        }

        if (!$this->listing || $this->listing->isDeletedStatus()) {
            return false;
        }

        return !$this->hasMessagingBlock($user);
    }
}