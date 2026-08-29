<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'base_currency',
        'quote_currency',
        'min_order_size',
        'price_precision',
        'quantity_precision',
        'status',
        'last_price',
        'change_24h',
        'high_24h',
        'low_24h',
        'volume_24h',
    ];

    protected $casts = [
        'min_order_size' => 'float',
        'last_price' => 'float',
        'change_24h' => 'float',
        'high_24h' => 'float',
        'low_24h' => 'float',
        'volume_24h' => 'float',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }
}
