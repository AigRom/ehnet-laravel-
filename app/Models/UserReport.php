<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    /**
     * Mass assignment lubatud väljad
     */
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'conversation_id',
        'reason',
        'details',
        'status',
    ];

    /**
     * Kasutaja, kes teate saatis
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Kasutaja, kelle kohta teade tehti
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Vestlus, mille kontekstis teade tehti
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}