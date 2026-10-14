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
        'username',
        'email',
        'password',
        'accepted_terms_at',
        'accepted_terms_ip',
        'is_admin',
        'is_moderator',
        'is_p2p_merchant',
        'is_dummy',
        'p2p_merchant_name',
        'p2p_payment_details',
        'p2p_completion_rate',
        'p2p_completed_trades',
        'p2p_rating',
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
            'is_dummy' => 'boolean',
            'p2p_completion_rate' => 'float',
            'p2p_completed_trades' => 'integer',
            'p2p_rating' => 'float',
            'demo_balance' => 'float',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->username)) {
                $user->username = static::generateUniqueUsername($user->name ?: 'Trader');
            }
        });
    }

    public static function generateUniqueUsername(string $baseName): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $baseName);
        if (empty($clean)) {
            $clean = 'Trader';
        }
        $username = $clean;
        $counter = 1;
        while (static::where('username', $username)->exists()) {
            $username = $clean . rand(10, 999);
            $counter++;
            if ($counter > 30) {
                $username = $clean . '_' . rand(1000, 9999);
                break;
            }
        }
        return $username;
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
