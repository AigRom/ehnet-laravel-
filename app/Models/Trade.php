<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    /**
     * Mass assignment jaoks lubatud väljad.
     */
    protected $fillable = [
        'conversation_id',
        'listing_id',
        'seller_id',
        'buyer_id',
        'status',
        'contact_revealed_at',
        'reserved_at',
        'completed_at',
        'buyer_confirmed_received_at',
        'cancelled_at',
    ];

    /**
     * Tüübimuundused.
     */
    protected $casts = [
        'conversation_id' => 'integer',
        'listing_id' => 'integer',
        'seller_id' => 'integer',
        'buyer_id' => 'integer',
        'contact_revealed_at' => 'datetime',
        'reserved_at' => 'datetime',
        'completed_at' => 'datetime',
        'buyer_confirmed_received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Tehinguga seotud vestlus.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Tehinguga seotud kuulutus.
     *
     * withTrashed() jätab seose alles ka siis,
     * kui kuulutus on soft delete'itud.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class)->withTrashed();
    }

    /**
     * Müüja.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Ostja.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Kas tehing on ostusoovi staatuses.
     */
    public function isInterest(): bool
    {
        return $this->status === 'interest';
    }

    /**
     * Kas tehing on broneeritud.
     */
    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    /**
     * Kas tehing on lõpetatud müüja poolt.
     *
     * completed tähendab siin, et kuulutus on müüduks märgitud.
     * Ostja kättesaamise kinnitus on eraldi samm.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Kas tehing on katkestatud.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Kas ostja on kinnitanud kauba kättesaamise.
     */
    public function isBuyerConfirmed(): bool
    {
        return $this->buyer_confirmed_received_at !== null;
    }

    /**
     * Kas tehing on aktiivne.
     *
     * Aktiivseks loeme interest ja reserved staatusega tehingud,
     * mis ei ole lõpetatud ega katkestatud.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['interest', 'reserved'], true)
            && !$this->isCancelled()
            && !$this->isCompleted();
    }

    /**
     * Kas tehingut saab reserveerida.
     */
    public function canBeReserved(): bool
    {
        return $this->isInterest()
            && !$this->isCancelled()
            && !$this->isCompleted();
    }

    /**
     * Kas tehingut saab lõpetada müügina.
     *
     * Seda teeb müüja reserved staatusest.
     */
    public function canBeCompleted(): bool
    {
        return $this->isReserved()
            && !$this->isCancelled()
            && !$this->isCompleted();
    }

    /**
     * Kas tehingut saab katkestada.
     *
     * Katkestada saab aktiivse interest või reserved tehingu.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['interest', 'reserved'], true)
            && !$this->isCancelled()
            && !$this->isCompleted();
    }

    /**
     * Kas ostja saab kinnitada kauba kättesaamist.
     *
     * Kinnitada saab ainult completed tehingu puhul,
     * kui ostja pole seda juba varem kinnitanud.
     */
    public function canBeConfirmedByBuyer(): bool
    {
        return $this->isCompleted() && !$this->isBuyerConfirmed();
    }

    /**
     * Kas kontaktandmed on selle tehingu puhul avatud.
     */
    public function contactsRevealed(): bool
    {
        return $this->contact_revealed_at !== null;
    }

    /**
     * Ostja nimi, kui buyer seos on olemas.
     */
    public function buyerName(): ?string
    {
        return $this->buyer?->name;
    }

    /**
     * Müüja nimi, kui seller seos on olemas.
     */
    public function sellerName(): ?string
    {
        return $this->seller?->name;
    }

    /**
     * Inimloetav tehingu staatuse nimetus.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'interest' => 'Ostusoov',
            'reserved' => 'Broneeritud',
            'completed' => 'Müüdud',
            'cancelled' => 'Katkestatud',
            default => '—',
        };
    }

    /**
     * Kas completed tehing ootab veel ostja kinnitust.
     */
    public function isAwaitingBuyerConfirmation(): bool
    {
        return $this->isCompleted() && !$this->isBuyerConfirmed();
    }

    /**
     * Tehinguga seotud tagasisided.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Kas antud kasutaja on selle tehingu osapool.
     */
    public function involvesUser(User $user): bool
    {
        return in_array($user->id, [$this->buyer_id, $this->seller_id], true);
    }

    /**
     * Kas sellele tehingule saab antud kasutaja tagasisidet jätta.
     *
     * Mõlemad osapooled saavad hinnata alles siis,
     * kui tehing on completed ja ostja on kauba kättesaamise kinnitanud.
     */
    public function canBeReviewedBy(User $user): bool
    {
        return $this->isCompleted()
            && $this->isBuyerConfirmed()
            && $this->involvesUser($user);
    }

    /**
     * Kellele antud kasutaja selle tehingu puhul hinnangu annab.
     */
    public function reviewTargetFor(User $user): ?User
    {
        if ($user->id === $this->buyer_id) {
            return $this->seller;
        }

        if ($user->id === $this->seller_id) {
            return $this->buyer;
        }

        return null;
    }

    /**
     * Kas antud kasutaja on sellele tehingule juba tagasiside jätnud.
     */
    public function hasReviewFrom(User $user): bool
    {
        return $this->reviews()
            ->where('reviewer_id', $user->id)
            ->exists();
    }

}