<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method',
        'phone_number',
        'currency',
        'amount',
        'kes_amount',
        'exchange_rate',
        'tx_hash',
        'mpesa_receipt',
        'transaction_request_id',
        'merchant_request_id',
        'checkout_request_id',
        'reference',
        'receipt_path',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'kes_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
