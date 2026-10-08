<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BinaryOptionContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'market_id',
        'direction',
        'entry_price',
        'strike_price',
        'investment_amount',
        'payout_rate',
        'payout_amount',
        'duration_seconds',
        'expires_at',
        'status',
        'is_demo',
    ];

    protected $casts = [
        'entry_price' => 'float',
        'strike_price' => 'float',
        'investment_amount' => 'float',
        'payout_rate' => 'float',
        'payout_amount' => 'float',
        'duration_seconds' => 'integer',
        'expires_at' => 'datetime',
        'is_demo' => 'boolean',
    ];

    protected $appends = [
        'amount',
        'payout',
        'settle_price',
        'settled_at',
    ];

    public function getAmountAttribute(): float
    {
        return (float) ($this->investment_amount ?? 0);
    }

    public function getPayoutAttribute(): float
    {
        return $this->status === 'win' ? (float) ($this->payout_amount ?? 0) : 0.0;
    }

    public function getSettlePriceAttribute(): float
    {
        return (float) ($this->strike_price ?? $this->entry_price ?? 0);
    }

    public function getSettledAtAttribute()
    {
        return $this->updated_at ?? $this->expires_at;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}
