<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auction extends Model
{
    protected $fillable = [
        'listing_id',
        'start_price',
        'min_increment',
        'starts_at',
        'ends_at',
        'reserve_price',
        'buy_now_price',
    ];

    protected $casts = [
        'listing_id'     => 'integer',
        'start_price'    => 'decimal:2',
        'min_increment'  => 'decimal:2',
        'reserve_price'  => 'decimal:2',
        'buy_now_price'  => 'decimal:2',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
