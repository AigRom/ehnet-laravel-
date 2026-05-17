<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    use HasFactory;

    public const SENDER_USER = 'user';
    public const SENDER_ADMIN = 'admin';
    public const SENDER_AI = 'ai';
    public const SENDER_SYSTEM = 'system';

    protected $fillable = [
        'support_thread_id',
        'user_id',
        'sender_type',
        'message',
    ];

    public function supportThread(): BelongsTo
    {
        return $this->belongsTo(SupportThread::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}