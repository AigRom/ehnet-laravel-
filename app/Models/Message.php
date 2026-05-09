<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    public const TYPE_USER = 'user';

    public const TYPE_SYSTEM = 'system';

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

    protected $casts = [
        'read_at' => 'datetime',
        'seller_read_at' => 'datetime',
        'buyer_read_at' => 'datetime',
        'meta' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isFrom(User $user): bool
    {
        return (int) $this->sender_id === (int) $user->id;
    }

    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    public function isUserMessage(): bool
    {
        return $this->type === self::TYPE_USER;
    }

    public function hasAttachments(): bool
    {
        if ($this->relationLoaded('attachments')) {
            return $this->attachments->isNotEmpty();
        }

        return $this->attachments()->exists();
    }

    public function imageAttachments()
    {
        return $this->attachments->where('type', 'image');
    }

    public function fileAttachments()
    {
        return $this->attachments->where('type', '!=', 'image');
    }

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
