<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Services\OrderBookEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@trade.com'],
            [
                'name' => 'Exchange Administrator',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'accepted_terms_at' => now(),
                'accepted_terms_ip' => '127.0.0.1',
            ]
        );

        // 2. Create Demo User
        $user = User::firstOrCreate(
            ['email' => 'user@trade.com'],
            [
                'name' => 'Demo Trader',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'accepted_terms_at' => now(),
                'accepted_terms_ip' => '127.0.0.1',
            ]
        );

        // 3. Create Demo User Wallets with balances
        $currencies = ['USDT', 'BTC', 'ETH', 'SOL', 'GOLD', 'NVDA', 'AAPL', 'TSLA'];
        foreach ($currencies as $curr) {
            Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => $curr],
                ['available_balance' => $curr === 'USDT' ? 25000.00 : 10.00, 'locked_balance' => 0]
            );
        }

        // 4. Create Markets List (Crypto, Commodities/Gold, Stocks)
        $marketsData = [
            // Cryptocurrencies
            ['symbol' => 'BTC/USDT', 'base' => 'BTC', 'quote' => 'USDT', 'price' => 64500.00, 'change' => 3.25, 'high' => 65200.00, 'low' => 63800.00, 'vol' => 245.80],
            ['symbol' => 'ETH/USDT', 'base' => 'ETH', 'quote' => 'USDT', 'price' => 3450.00, 'change' => -1.15, 'high' => 3520.00, 'low' => 3410.00, 'vol' => 1840.50],
            ['symbol' => 'BNB/USDT', 'base' => 'BNB', 'quote' => 'USDT', 'price' => 580.00, 'change' => 5.40, 'high' => 595.00, 'low' => 565.00, 'vol' => 5400.00],
            ['symbol' => 'SOL/USDT', 'base' => 'SOL', 'quote' => 'USDT', 'price' => 148.50, 'change' => 7.80, 'high' => 152.00, 'low' => 139.50, 'vol' => 12400.00],
            ['symbol' => 'XRP/USDT', 'base' => 'XRP', 'quote' => 'USDT', 'price' => 0.5850, 'change' => 2.10, 'high' => 0.6100, 'low' => 0.5700, 'vol' => 85000.00],
            ['symbol' => 'DOGE/USDT', 'base' => 'DOGE', 'quote' => 'USDT', 'price' => 0.1240, 'change' => 4.50, 'high' => 0.1300, 'low' => 0.1180, 'vol' => 340000.00],

            // Commodities & Precious Metals
            ['symbol' => 'GOLD/USDT', 'base' => 'GOLD', 'quote' => 'USDT', 'price' => 2512.40, 'change' => 1.12, 'high' => 2525.00, 'low' => 2495.00, 'vol' => 4800.00],
            ['symbol' => 'SILVER/USDT', 'base' => 'SILVER', 'quote' => 'USDT', 'price' => 29.85, 'change' => 0.85, 'high' => 30.20, 'low' => 29.40, 'vol' => 18200.00],
            ['symbol' => 'OIL/USDT', 'base' => 'OIL', 'quote' => 'USDT', 'price' => 75.40, 'change' => -0.65, 'high' => 76.80, 'low' => 74.90, 'vol' => 9200.00],

            // Stocks & Indices
            ['symbol' => 'NVDA/USDT', 'base' => 'NVDA', 'quote' => 'USDT', 'price' => 128.50, 'change' => 4.25, 'high' => 131.00, 'low' => 124.80, 'vol' => 45000.00],
            ['symbol' => 'AAPL/USDT', 'base' => 'AAPL', 'quote' => 'USDT', 'price' => 224.20, 'change' => 0.95, 'high' => 226.50, 'low' => 222.10, 'vol' => 28000.00],
            ['symbol' => 'TSLA/USDT', 'base' => 'TSLA', 'quote' => 'USDT', 'price' => 210.80, 'change' => -2.40, 'high' => 218.00, 'low' => 208.50, 'vol' => 31000.00],
            ['symbol' => 'MSFT/USDT', 'base' => 'MSFT', 'quote' => 'USDT', 'price' => 415.60, 'change' => 1.45, 'high' => 419.00, 'low' => 411.20, 'vol' => 19000.00],
            ['symbol' => 'SPY/USDT', 'base' => 'SPY', 'quote' => 'USDT', 'price' => 560.20, 'change' => 0.78, 'high' => 562.50, 'low' => 558.00, 'vol' => 62000.00],
        ];

        $btcMarket = null;

        foreach ($marketsData as $m) {
            $created = Market::firstOrCreate(
                ['symbol' => $m['symbol']],
                [
                    'base_currency' => $m['base'],
                    'quote_currency' => $m['quote'],
                    'min_order_size' => 0.0001,
                    'price_precision' => 2,
                    'quantity_precision' => 4,
                    'last_price' => $m['price'],
                    'change_24h' => $m['change'],
                    'high_24h' => $m['high'],
                    'low_24h' => $m['low'],
                    'volume_24h' => $m['vol'],
                ]
            );

            if ($m['symbol'] === 'BTC/USDT') {
                $btcMarket = $created;
            }
        }

        // 5. Seed Initial Bids and Asks for BTC/USDT in OrderBook
        if ($btcMarket) {
            try {
                $bids = [
                    ['price' => 64480.00, 'qty' => 0.45],
                    ['price' => 64450.00, 'qty' => 1.20],
                    ['price' => 64400.00, 'qty' => 0.85],
                    ['price' => 64350.00, 'qty' => 2.10],
                    ['price' => 64300.00, 'qty' => 3.50],
                ];
                foreach ($bids as $b) {
                    $o = Order::create([
                        'user_id' => $admin->id,
                        'market_id' => $btcMarket->id,
                        'side' => 'buy',
                        'type' => 'limit',
                        'price' => $b['price'],
                        'quantity' => $b['qty'],
                        'filled_quantity' => 0,
                        'status' => 'open',
                    ]);
                    Redis::zadd("orderbook:{$btcMarket->id}:bids", $b['price'], $o->id);
                    Redis::hset("orderbook:{$btcMarket->id}:details", "order_{$o->id}", json_encode([
                        'id' => $o->id, 'user_id' => $admin->id, 'side' => 'buy', 'price' => $b['price'], 'quantity' => $b['qty'], 'remaining_quantity' => $b['qty']
                    ]));
                }

                $asks = [
                    ['price' => 64520.00, 'qty' => 0.35],
                    ['price' => 64550.00, 'qty' => 0.90],
                    ['price' => 64600.00, 'qty' => 1.50],
                    ['price' => 64650.00, 'qty' => 2.80],
                    ['price' => 64700.00, 'qty' => 4.10],
                ];
                foreach ($asks as $a) {
                    $o = Order::create([
                        'user_id' => $admin->id,
                        'market_id' => $btcMarket->id,
                        'side' => 'sell',
                        'type' => 'limit',
                        'price' => $a['price'],
                        'quantity' => $a['qty'],
                        'filled_quantity' => 0,
                        'status' => 'open',
                    ]);
                    Redis::zadd("orderbook:{$btcMarket->id}:asks", $a['price'], $o->id);
                    Redis::hset("orderbook:{$btcMarket->id}:details", "order_{$o->id}", json_encode([
                        'id' => $o->id, 'user_id' => $admin->id, 'side' => 'sell', 'price' => $a['price'], 'quantity' => $a['qty'], 'remaining_quantity' => $a['qty']
                    ]));
                }
            } catch (\Exception $e) {}
        }
    }
}
