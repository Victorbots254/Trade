<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'amount',
        'bep20_address',
        'tx_hash',
        'status',
        'admin_notes',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
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
}
