<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2POrder extends Model
{
    use HasFactory;

    protected $table = 'p2p_orders';

    protected $fillable = [
        'order_number',
        'ad_id',
        'buyer_id',
        'seller_id',
        'crypto_amount',
        'fiat_amount',
        'price',
        'payment_method',
        'payment_details',
        'status', // 'pending_payment', 'paid', 'completed', 'cancelled', 'disputed'
        'expires_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'disputed_at',
        'disputed_by',
        'dispute_reason',
    ];

    protected function casts(): array
    {
        return [
            'crypto_amount' => 'float',
            'fiat_amount' => 'float',
            'price' => 'float',
            'payment_details' => 'array',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'disputed_at' => 'datetime',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(P2PAd::class, 'ad_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(P2PMessage::class, 'order_id');
    }

    public function disputer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disputed_by');
    }
}
