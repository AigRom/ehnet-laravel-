<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Listing extends Model
{
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
     * Aktiivsed tehingud.
     *
     * Uue flow järgi loeme aktiivseks:
     * - interest
     * - reserved
     * - awaiting_confirmation
     */
    public function activeTrades(): HasMany
    {
        return $this->hasMany(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation']);
    }

    /**
     * Hetkel reserveeritud tehing.
     *
     * Reserved tähendab siin “ostjale kinni pandud, aga veel mitte üleantud”.
     */
    public function reservedTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'reserved')
            ->latestOfMany();
    }

    /**
     * Tehing, mis ootab ostja kinnitust.
     *
     * See tekib pärast seda, kui müüja märkis kauba üleantuks / teele panduks.
     */
    public function awaitingConfirmationTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->where('status', 'awaiting_confirmation')
            ->latestOfMany();
    }

    /**
     * Viimane aktiivne tehing.
     */
    public function latestActiveTrade(): HasOne
    {
        return $this->hasOne(Trade::class)
            ->whereIn('status', ['interest', 'reserved', 'awaiting_confirmation'])
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
     * Tagastab kaanepildi täissuuruses URL-i.
     */
    public function coverImageUrl(): ?string
    {
        $img = $this->coverImage();

        return $img ? $img->url() : null;
    }

    /**
     * Tagastab kaanepildi thumbnail URL-i.
     */
    public function coverThumbUrl(): ?string
    {
        $img = $this->coverImage();

        return $img ? $img->thumbUrl() : null;
    }

    /**
     * Tagastab kuulutuse pildid detailvaate jaoks struktureeritud kujul.
     *
     * @return array<int, array{full: string, thumb: string}>
     */
    public function detailImageItems(): array
    {
        if (!$this->relationLoaded('images')) {
            $this->load('images');
        }

        return $this->images
            ->map(fn ($img) => [
                'full' => $img->url(),
                'thumb' => $img->thumbUrl(),
            ])
            ->filter(fn ($item) => !empty($item['full']))
            ->values()
            ->all();
    }

    /**
     * Kui kuulutusel pilti ei ole, kasutatakse placeholder pilti.
     */
    public function coverThumbUrlOrPlaceholder(): string
    {
        return $this->coverThumbUrl() ?? asset('images/placeholder.png');
    }

    /**
     * Kas kuulutusel on süsteemi jaoks säilitamist vajavaid seoseid.
     */
    public function hasRelations(): bool
    {
        return $this->favoritedBy()->exists()
            || $this->conversations()->exists()
            || $this->trades()->exists();
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
     * NB! See on äriloogika staatus, mitte Laravel soft delete.
     */
    public function isDeletedStatus(): bool
    {
        return $this->status === 'deleted';
    }

    /**
     * Kas kuulutus on avalikus vaates nähtav.
     *
     * - published => peab olema avaldatud ja mitte aegunud
     * - reserved  => jääb avalikuks ka siis, kui expires_at oleks minevikus
     */
    public function isPublicVisible(): bool
    {
        if ($this->status === 'reserved') {
            return $this->published_at !== null;
        }

        return $this->status === 'published'
            && $this->published_at !== null
            && ($this->expires_at === null || $this->expires_at->isFuture() || $this->expires_at->isToday());
    }

    /**
     * Kas kuulutus on aktiivne published kuulutus.
     *
     * Reserved ei ole aktiivne published kuulutus, kuigi ta on avalik.
     */
    public function isActivePublished(): bool
    {
        return $this->status === 'published' && $this->isPublicVisible();
    }

    /**
     * Kas kuulutus saab vastu võtta uut ostusoovi.
     */
    public function canAcceptTradeInterest(): bool
    {
        return $this->isActivePublished();
    }

    /**
     * Kas kuulutust saab reserveerida.
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
     * Kas kuulutusel on tehing, mis ootab ostja kinnitust.
     */
    public function hasAwaitingConfirmationTrade(): bool
    {
        if ($this->relationLoaded('awaitingConfirmationTrade')) {
            return $this->awaitingConfirmationTrade !== null;
        }

        return $this->trades()
            ->where('status', 'awaiting_confirmation')
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
     * NB! Ka awaiting_confirmation etapis ei tohiks omanik kuulutust muuta,
     * sest tehing on juba lõppfaasis.
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
     * 1. awaitingConfirmationTrade buyer
     * 2. reservedTrade buyer
     * 3. soldTrade buyer
     * 4. soldToUser
     */
    public function tradeBuyerName(): ?string
    {
        return $this->awaitingConfirmationTrade?->buyerName()
            ?? $this->reservedTrade?->buyerName()
            ?? $this->soldTrade?->buyerName()
            ?? $this->soldToUser?->name
            ?? null;
    }

    /**
     * Tagastab halduses eelistatud vestluse.
     *
     * Eelistus:
     * 1. ostja kinnitust ootava tehingu vestlus
     * 2. reserveeritud tehingu vestlus
     * 3. lõpetatud müügitehingu vestlus
     * 4. viimane aktiivne tehingu vestlus
     */
    public function preferredConversation(): ?Conversation
    {
        return $this->awaitingConfirmationTrade?->conversation
            ?? $this->reservedTrade?->conversation
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
     * NB! Kuna listing.status jääb reserved kuni ostja kinnitab,
     * eristame siin helperis kahte olukorda:
     * - reserved + reservedTrade => Broneeritud: Mari
     * - reserved + awaitingConfirmationTrade => Ootab kinnitust: Mari
     */
    public function statusHelpText(): ?string
    {
        $buyerName = $this->tradeBuyerName();

        return match (true) {
            $this->status === 'reserved' && $this->hasAwaitingConfirmationTrade() && $buyerName !== null
                => __('Ootab kinnitust: :name', ['name' => $buyerName]),

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
     */
    public function showExpiryDate(): bool
    {
        return $this->status === 'published' && $this->expires_at !== null;
    }

    /**
     * Tagastab aegumise prefiksi.
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
     *
     * Lubame sõnumi jätkamist nii reserved kui awaiting_confirmation faasis,
     * sest listing ise jääb selle aja jooksul reserved staatuse alla.
     */
    public function isReservedForUser(?User $user): bool
    {
        if (!$user || !$this->isReserved()) {
            return false;
        }

        $trade = $this->awaitingConfirmationTrade
            ?? $this->reservedTrade
            ?? null;

        if (!$trade) {
            return false;
        }

        return (int) $trade->buyer_id === (int) $user->id;
    }

    /**
     * Kas kasutaja võib selle kuulutuse puhul sõnumi saata.
     *
     * - aktiivne published => võib saata iga huviline
     * - reserved => võib jätkata ainult seotud ostja
     * - muu staatus => ei või
     */
    public function canBuyerSendMessage(?User $viewer = null): bool
    {
        if ($this->isActivePublished()) {
            return true;
        }

        if ($this->isReserved()) {
            return $this->isReservedForUser($viewer);
        }

        return false;
    }

    /**
     * Kas kasutaja võib esitada ostusoovi.
     *
     * Ostusoovi saab esitada ainult aktiivsele published kuulutusele.
     */
    public function canBuyerExpressBuyIntent(): bool
    {
        return $this->isActivePublished();
    }

    /**
     * Scope avalikult nähtavate kuulutuste jaoks.
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where(function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('status', 'published')
                        ->where(function (Builder $qq) {
                            $qq->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        });
                })->orWhere('status', 'reserved');
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