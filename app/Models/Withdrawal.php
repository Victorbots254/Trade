<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'amount',
        'payment_method',
        'bep20_address',
        'mpesa_phone',
        'mpesa_name',
        'mpesa_type',
        'mpesa_account_number',
        'amount_kes',
        'tx_hash',
        'status',
        'admin_notes',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'amount_kes' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $appends = [
        'bscscan_url',
        'address_explorer_url',
        'destination_label',
        'payment_method_label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBscscanUrlAttribute()
    {
        return $this->tx_hash ? "https://bscscan.com/tx/{$this->tx_hash}" : null;
    }

    public function getAddressExplorerUrlAttribute()
    {
        return $this->bep20_address ? "https://bscscan.com/address/{$this->bep20_address}" : null;
    }

    public function getDestinationLabelAttribute()
    {
        if ($this->payment_method === 'mpesa') {
            $type = $this->mpesa_type === 'paybill' ? 'Paybill' : 'Phone';
            $num = $this->mpesa_type === 'paybill' ? "{$this->mpesa_phone} (Acc: {$this->mpesa_account_number})" : $this->mpesa_phone;
            return "M-Pesa {$type}: {$num}" . ($this->mpesa_name ? " - {$this->mpesa_name}" : '');
        }

        return $this->bep20_address ?: 'BEP-20';
    }

    public function getPaymentMethodLabelAttribute()
    {
        return $this->payment_method === 'mpesa' ? 'Safaricom M-Pesa' : 'Binance BEP-20';
    }
}
