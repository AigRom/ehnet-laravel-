<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Review;

class Message extends Model
{
    /**
     * Message tüübid.
     */
    public const TYPE_USER = 'user';
    public const TYPE_SYSTEM = 'system';

    /**
     * Mass assignment jaoks lubatud väljad.
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'meta',       
        'read_at',
        'seller_read_at',
        'buyer_read_at',
    ];

    /**
     * Tüübimuundused.
     */
    protected $casts = [
        'read_at' => 'datetime',
        'seller_read_at' => 'datetime',
        'buyer_read_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Vestlus, kuhu sõnum kuulub.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Sõnumi saatja.
     *
     * NB: süsteemisõnumil võib olla null.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Kõik sõnumi manused.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Kas sõnum on loetuks märgitud.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Kas sõnum on antud kasutajalt.
     */
    public function isFrom(User $user): bool
    {
        return (int) $this->sender_id === (int) $user->id;
    }

    /**
     * Kas tegemist on süsteemisõnumiga.
     */
    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    /**
     * Kas tegemist on kasutaja sõnumiga.
     */
    public function isUserMessage(): bool
    {
        return $this->type === self::TYPE_USER;
    }

    /**
     * Kas sõnumil on manuseid.
     */
    public function hasAttachments(): bool
    {
        if ($this->relationLoaded('attachments')) {
            return $this->attachments->isNotEmpty();
        }

        return $this->attachments()->exists();
    }

    /**
     * Tagastab ainult pildimanused.
     */
    public function imageAttachments()
    {
        return $this->attachments->where('type', 'image');
    }

    /**
     * Tagastab kõik mitte-pildi manused.
     */
    public function fileAttachments()
    {
        return $this->attachments->where('type', '!=', 'image');
    }

    /**
 * Tagastab süsteemisõnumiga seotud review, kui see on olemas.
 */
    public function relatedReview(): ?Review
    {
        static $cache = [];

        $reviewId = data_get($this->meta, 'review_id');

        if (! $reviewId) {
            return null;
        }

        if (! array_key_exists($reviewId, $cache)) {
            $cache[$reviewId] = Review::with(['reviewer', 'reviewedUser', 'trade.listing'])->find($reviewId);
        }

        return $cache[$reviewId];
}
}