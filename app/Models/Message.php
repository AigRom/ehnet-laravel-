<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    /**
     * Mass assignment jaoks lubatud väljad.
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'read_at',
    ];

    /**
     * Tüübimuundused.
     */
    protected $casts = [
        'read_at' => 'datetime',
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
     * Kas sõnumil on manuseid.
     *
     * Kui attachments seos on juba eager loaditud, kasutame seda.
     * Vastasel juhul teeme exists() päringu.
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
     *
     * Eeldab, et attachments seos on eager loaditud või laetud.
     */
    public function imageAttachments()
    {
        return $this->attachments->where('type', 'image');
    }

    /**
     * Tagastab kõik mitte-pildi manused.
     *
     * Eeldab, et attachments seos on eager loaditud või laetud.
     */
    public function fileAttachments()
    {
        return $this->attachments->where('type', '!=', 'image');
    }
}