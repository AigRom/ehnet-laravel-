<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBlock extends Model
{
    /**
     * Mass assignment lubatud väljad
     */
    protected $fillable = [
        'blocker_id',
        'blocked_user_id',
    ];

    /**
     * Kasutaja, kes blokeerimise tegi
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    /**
     * Kasutaja, kes blokeeriti
     */
    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}