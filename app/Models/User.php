<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'accepted_terms_at',
        'accepted_terms_ip',
        'is_admin',
        'is_moderator',
        'is_p2p_merchant',
        'p2p_merchant_name',
        'p2p_payment_details',
        'p2p_completion_rate',
        'p2p_completed_trades',
        'demo_balance',
        'trading_outcome_mode',
        'bep20_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'accepted_terms_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_moderator' => 'boolean',
            'is_p2p_merchant' => 'boolean',
            'p2p_completion_rate' => 'float',
            'p2p_completed_trades' => 'integer',
            'demo_balance' => 'float',
            'password' => 'hashed',
        ];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function binaryOptionContracts(): HasMany
    {
        return $this->hasMany(BinaryOptionContract::class);
    }

    public function p2pAds(): HasMany
    {
        return $this->hasMany(P2PAd::class);
    }

    public function p2pBuyerOrders(): HasMany
    {
        return $this->hasMany(P2POrder::class, 'buyer_id');
    }

    public function p2pSellerOrders(): HasMany
    {
        return $this->hasMany(P2POrder::class, 'seller_id');
    }
}
