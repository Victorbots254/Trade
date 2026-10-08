<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PAd extends Model
{
    use HasFactory;

    protected $table = 'p2p_ads';

    protected $fillable = [
        'user_id',
        'type', // 'sell' (user buys from merchant), 'buy' (user sells to merchant)
        'asset',
        'fiat',
        'price',
        'total_amount',
        'available_amount',
        'min_limit',
        'max_limit',
        'payment_methods',
        'payment_details',
        'terms',
        'auto_reply',
        'time_limit_minutes',
        'status', // 'active', 'paused', 'closed'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'total_amount' => 'float',
            'available_amount' => 'float',
            'min_limit' => 'float',
            'max_limit' => 'float',
            'payment_methods' => 'array',
            'payment_details' => 'array',
            'time_limit_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(P2POrder::class, 'ad_id');
    }
}
