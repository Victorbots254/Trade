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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}
