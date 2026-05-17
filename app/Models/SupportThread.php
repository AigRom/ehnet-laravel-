<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportThread extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_CLOSED = 'closed';

    public const CATEGORY_PROBLEM = 'problem';
    public const CATEGORY_LISTING = 'listing';
    public const CATEGORY_ACCOUNT = 'account';
    public const CATEGORY_FEEDBACK = 'feedback';
    public const CATEGORY_SUGGESTION = 'suggestion';
    public const CATEGORY_GENERAL = 'general';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'category',
        'subject',
        'status',
        'priority',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }
}