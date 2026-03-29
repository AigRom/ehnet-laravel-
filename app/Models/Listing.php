<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Listing extends Model
{
    use SoftDeletes;

    /**
     * Mass assignment jaoks lubatud väljad.
     */
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
        'listing_type',   // sale|auction
        'status',         // draft|pending|published|reserved|rejected|archived|sold|deleted
        'sold_to_user_id',
        'sold_trade_id',
        'published_at',
        'expires_at',
        'reviewed_by',
        'reviewed_at',
        'rejected_reason',
        'delivery_options',
    ];

    /**
     * Tüübimuundused.
     */
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
        'deleted_at'       => 'datetime',
    ];

    /**
     * Kuulutuse omanik.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kuulutuse kategooria.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Kuulutuse asukoht.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Kõik kuulutuse pildid sort_order järgi.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    /**
     * Oksjonikirje, kui tegu on oksjonitüüpi kuulutusega.
     */
    public function auction(): HasOne
    {
        return $this->hasOne(Auction::class);
    }

    /**
     * Kasutajad, kes on kuulutuse lemmikuks märkinud.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Kuulutusega seotud vestlused.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Kõik kuulutusega seotud tehingud.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    /**
     * Aktiivsed tehingud: ostusoov või broneering.
     */
    public function activeTrades(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->whereIn('status', ['interest', 'reserved']);
    }

    /**
     * Hetkel aktiivne reserveeritud tehing.
     *
     * latestOfMany() aitab vältida olukorda, kus mitme kirje korral
     * tagastuks ebamäärane reserved trade.
     */
    public function reservedTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'reserved')
            ->latestOfMany();
    }

    /**
     * Viimane aktiivne tehing (interest või reserved).
     */
    public function latestActiveTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->whereIn('status', ['interest', 'reserved'])
            ->latestOfMany();
    }

    /**
     * Kasutaja, kellele kuulutus müüdi.
     */
    public function soldToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }

    /**
     * Lõpetatud tehing, mille kaudu kuulutus müüdi.
     */
    public function soldTrade(): BelongsTo
    {
        return $this->belongsTo(Trade::class, 'sold_trade_id');
    }

    /**
     * Esimene pilt ehk kaanepilt.
     */
    public function coverImage(): ?ListingImage
    {
        return $this->images()->first();
    }

    /**
     * Kaanepildi avalik URL.
     */
    public function coverImageUrl(): ?string
    {
        $img = $this->coverImage();

        return $img ? Storage::url($img->path) : null;
    }

    /**
     * Kas kuulutus on aegunud.
     *
     * Aegumise loogika kehtib ainult published staatuse puhul.
     */
    public function isExpired(): bool
    {
        return $this->status === 'published'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Kas kuulutus on broneeritud.
     */
    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    /**
     * Kas kuulutus on müüdud.
     */
    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    /**
     * Kas kuulutus on märgitud kustutatuks.
     *
     * NB! See on äriloogika staatus, mitte sama mis soft delete.
     */
    public function isDeletedStatus(): bool
    {
        return $this->status === 'deleted';
    }

    /**
     * Kas kuulutus on avalikus vaates nähtav.
     */
    public function isPublicVisible(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && ($this->expires_at === null || $this->expires_at->isFuture() || $this->expires_at->isToday());
    }

    /**
     * Kas kuulutus on aktiivne published kuulutus.
     */
    public function isActivePublished(): bool
    {
        return $this->isPublicVisible();
    }

    /**
     * Kas kuulutus saab vastu võtta uut ostusoovi.
     *
     * Ostusoovi saab esitada ainult aktiivsele published kuulutusele.
     */
    public function canAcceptTradeInterest(): bool
    {
        return $this->isActivePublished();
    }

    /**
     * Kas kuulutust saab reserveerida.
     *
     * Reserveerida saab ainult aktiivset published kuulutust.
     */
    public function canAcceptTradeReservation(): bool
    {
        return $this->isActivePublished();
    }

    /**
     * Kas kuulutusel on aktiivne tehing.
     */
    public function hasActiveTrade(): bool
    {
        if ($this->relationLoaded('latestActiveTrade')) {
            return $this->latestActiveTrade !== null;
        }

        return $this->activeTrades()->exists();
    }

    /**
     * Kas kuulutusel on reserveeritud tehing.
     */
    public function hasReservedTrade(): bool
    {
        if ($this->relationLoaded('reservedTrade')) {
            return $this->reservedTrade !== null;
        }

        return $this->trades()
            ->where('status', 'reserved')
            ->exists();
    }

    /**
     * Kas omanik tohib kuulutust muuta.
     *
     * Muuta ei tohi:
     * - kustutatud kuulutust
     * - broneeritud kuulutust
     * - müüdud kuulutust
     *
     * Muuta tohib ka aegunud published kuulutust.
     */
    public function canBeEditedByOwner(): bool
    {
        if (in_array($this->status, ['deleted', 'reserved', 'sold'], true)) {
            return false;
        }

        return in_array($this->status, ['draft', 'pending', 'published', 'archived', 'rejected'], true);
    }

    /**
     * Kas omanik tohib kuulutuse kustutada.
     */
    public function canBeDeletedByOwner(): bool
    {
        return !in_array($this->status, ['reserved', 'sold', 'deleted'], true);
    }

    /**
     * Kas omanik tohib kuulutust peatada või taasaktiveerida.
     *
     * Lubatud ainult:
     * - published
     * - archived
     *
     * Aegunud kuulutust ei peatata enam, vaid pannakse uuesti müüki.
     */
    public function canBeToggledByOwner(): bool
    {
        if (!in_array($this->status, ['published', 'archived'], true)) {
            return false;
        }

        return !$this->isExpired();
    }

    /**
     * Inimloetav staatuse nimetus.
     *
     * Published puhul eristame aktiivset ja aegunud kuulutust.
     */
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

    /**
     * Tagastab tehinguga seotud ostja nime.
     *
     * Eelistus:
     * 1. reservedTrade buyer
     * 2. soldTrade buyer
     * 3. soldToUser
     */
    public function tradeBuyerName(): ?string
    {
        return $this->reservedTrade?->buyerName()
            ?? $this->soldTrade?->buyerName()
            ?? $this->soldToUser?->name
            ?? null;
    }

    /**
     * Tagastab halduses eelistatud vestluse.
     *
     * Eelistus:
     * 1. reserveeritud tehingu vestlus
     * 2. lõpetatud müügitehingu vestlus
     * 3. viimane aktiivne tehingu vestlus
     */
    public function preferredConversation(): ?Conversation
    {
        return $this->reservedTrade?->conversation
            ?? $this->soldTrade?->conversation
            ?? $this->latestActiveTrade?->conversation
            ?? null;
    }

    /**
     * Tagastab halduses kasutatava vestluse URL-i.
     */
    public function conversationUrl(): ?string
    {
        $conversation = $this->preferredConversation();

        return $conversation ? route('messages.show', $conversation) : null;
    }

    /**
     * Tagastab lühikese abiteksti staatuse kõrvale.
     *
     * Näited:
     * - Broneeritud: Mari
     * - Müüdud: Jaan
     * - Huvi tundnud: Peeter
     * - Aegus: 12.04.2026
     */
    public function statusHelpText(): ?string
    {
        $buyerName = $this->tradeBuyerName();

        return match (true) {
            $this->status === 'reserved' && $buyerName !== null
                => __('Broneeritud: :name', ['name' => $buyerName]),

            $this->status === 'sold' && $buyerName !== null
                => __('Müüdud: :name', ['name' => $buyerName]),

            $this->latestActiveTrade?->buyer?->name !== null
                => __('Huvi tundnud: :name', ['name' => $this->latestActiveTrade->buyer->name]),

            $this->isExpired() && $this->expires_at !== null
                => __('Aegus: :date', ['date' => $this->expires_at->format('d.m.Y')]),

            $this->status === 'deleted'
                => __('Kuulutus on kustutatud'),

            default => null,
        };
    }

    /**
     * Kas aegumiskuupäeva peaks view's näitama.
     *
     * Aegumise info on sisuline ainult published kuulutuse puhul.
     */
    public function showExpiryDate(): bool
    {
        return $this->status === 'published' && $this->expires_at !== null;
    }

    /**
     * Tagastab aegumise prefiksi.
     *
     * Võimalikud väärtused:
     * - Aegub:
     * - Aegus:
     */
    public function expiryLabel(): ?string
    {
        if (!$this->showExpiryDate()) {
            return null;
        }

        return $this->isExpired() ? __('Aegus:') : __('Aegub:');
    }

    /**
     * Tagastab vormindatud aegumiskuupäeva.
     */
    public function expiryDateText(): ?string
    {
        if (!$this->showExpiryDate()) {
            return null;
        }

        return $this->expires_at?->format('d.m.Y');
    }

    /**
     * Tagastab hinna inimloetaval kujul.
     *
     * Näited:
     * - Kokkuleppel
     * - Tasuta
     * - 25 EUR
     * - 25.5 EUR
     */
    public function priceLabel(): string
    {
        if ($this->price === null) {
            return __('Kokkuleppel');
        }

        if ((float) $this->price === 0.0) {
            return __('Tasuta');
        }

        return rtrim(rtrim(number_format((float) $this->price, 2, '.', ''), '0'), '.')
            . ' '
            . ($this->currency ?? 'EUR');
    }

    /**
     * Tagastab tarneviisid inimloetavate siltidena.
     */
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

    /**
     * Tagastab seisukorra inimloetava nimetuse.
     */
    public function conditionLabel(): string
    {
        return match ($this->condition) {
            'new'      => 'Uus',
            'used'     => 'Kasutatud',
            'leftover' => 'Jääk',
            default    => '—',
        };
    }

    /**
     * Kas reserveeritud kuulutus on broneeritud konkreetsele kasutajale.
     */
    public function isReservedForUser(?User $user): bool
    {
        if (!$user || !$this->isReserved() || !$this->reservedTrade) {
            return false;
        }

        return (int) $this->reservedTrade->buyer_id === (int) $user->id;
    }

    /**
     * Kas ostja võib selle kuulutuse puhul sõnumi saata.
     *
     * Ostja vaates:
     * - aktiivne published => võib saata
     * - reserved => võib jätkata suhtlust
     */
    public function canBuyerSendMessage(): bool
    {
        return $this->isActivePublished() || $this->isReserved();
    }

    /**
     * Kas ostja võib esitada ostusoovi.
     *
     * Ostusoovi saab esitada ainult aktiivsele published kuulutusele.
     */
    public function canBuyerExpressBuyIntent(): bool
    {
        return $this->isActivePublished();
    }

    /**
     * Tagastab müüjakaardi saadavuse teavitusteksti.
     *
     * Kasutatakse seller-card komponendis nii ostja kui müüja vaates.
     */
    public function sellerCardAvailabilityMessage(?User $viewer = null): ?string
    {
        $reservedBuyer = $this->reservedTrade?->buyer;
        $soldBuyer = $this->soldTrade?->buyer;

        if ($this->isDeletedStatus()) {
            return __('See kuulutus on kustutatud.');
        }

        if ($this->isSold()) {
            return $soldBuyer
                ? __('See kuulutus on müüdud kasutajale :name.', ['name' => $soldBuyer->name])
                : __('See kuulutus on müüdud.');
        }

        if ($this->status === 'archived') {
            return __('See kuulutus on müügist eemaldatud.');
        }

        if ($this->isExpired()) {
            return __('See kuulutus on aegunud.');
        }

        if ($this->isReserved()) {
            if ($this->isReservedForUser($viewer)) {
                return __('See kuulutus on broneeritud sulle. Võta müüjaga ühendust ja vii tehing lõpuni.');
            }

            return $reservedBuyer
                ? __('See kuulutus on broneeritud kasutajale :name.', ['name' => $reservedBuyer->name])
                : __('See kuulutus on broneeritud teisele ostjale.');
        }

        return null;
    }

    /**
     * Tagastab müüjakaardi teavituse stiiliklassid.
     */
    public function sellerCardAvailabilityClasses(?User $viewer = null): string
    {
        if ($this->isDeletedStatus()) {
            return 'border-red-200 bg-red-50 text-red-700';
        }

        if ($this->isSold()) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700';
        }

        if ($this->status === 'archived') {
            return 'border-zinc-200 bg-zinc-50 text-zinc-600';
        }

        if ($this->isExpired()) {
            return 'border-amber-200 bg-amber-50 text-amber-700';
        }

        if ($this->isReserved()) {
            return $this->isReservedForUser($viewer)
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-amber-200 bg-amber-50 text-amber-700';
        }

        return 'border-zinc-200 bg-zinc-50 text-zinc-600';
    }

    /**
     * Scope avalikult nähtavate kuulutuste jaoks.
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope avalehe feedi jaoks.
     */
    public function scopeHomeFeed(Builder $query): Builder
    {
        return $query
            ->publicVisible()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}