<?php

namespace Database\Seeders;

use App\Models\P2PAd;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class P2PLiquiditySeeder extends Seeder
{
    /**
     * Run the database seeds for realistic P2P merchants, usernames, and liquidity ads.
     */
    public function run(bool $clearDummy = true): void
    {
        if ($clearDummy) {
            $this->command?->info('Clearing old dummy P2P ads and users...');
            P2PAd::where('is_dummy', true)->delete();
            User::where('is_dummy', true)->delete();
        }

        // -------------------------------------------------------------
        // 1. ASSIGN UNIQUE USERNAMES & VARIED STATS TO REAL USERS
        // -------------------------------------------------------------
        $realUsers = User::where('is_dummy', false)->get();

        $realUserCustomStats = [
            'michaelkairithia@gmail.com' => [
                'username' => 'Mikeshi07',
                'merchant_name' => 'Mikeshi07',
                'completed_trades' => 8420,
                'completion_rate' => 100.00,
                'rating' => 5.00,
                'is_premier' => true,
            ],
            'vikigitonga14@gmail.com' => [
                'username' => 'VictorFX_254',
                'merchant_name' => 'VictorFX_254',
                'completed_trades' => 6150,
                'completion_rate' => 100.00,
                'rating' => 5.00,
                'is_premier' => true,
            ],
            'vikigitonga12@gmail.com' => [
                'username' => 'VikiG_OTC',
                'merchant_name' => 'VikiG_OTC',
                'completed_trades' => 1840,
                'completion_rate' => 98.80,
                'rating' => 4.90,
                'is_premier' => false,
            ],
            'michaelkairithia2@gmail.com' => [
                'username' => 'CarbonateFX',
                'merchant_name' => 'CarbonateFX',
                'completed_trades' => 920,
                'completion_rate' => 97.90,
                'rating' => 4.85,
                'is_premier' => false,
            ],
            'michaelkairithia3@gmail.com' => [
                'username' => 'MikeCarbonate',
                'merchant_name' => 'MikeCarbonate',
                'completed_trades' => 740,
                'completion_rate' => 98.40,
                'rating' => 4.88,
                'is_premier' => false,
            ],
            'vikigitonga11@gmail.com' => [
                'username' => 'VicTrading',
                'merchant_name' => 'VicTrading',
                'completed_trades' => 3120,
                'completion_rate' => 99.10,
                'rating' => 4.92,
                'is_premier' => false,
            ],
            'vikigitonga15@gmail.com' => [
                'username' => 'OrgenExpress',
                'merchant_name' => 'OrgenExpress',
                'completed_trades' => 510,
                'completion_rate' => 97.50,
                'rating' => 4.80,
                'is_premier' => false,
            ],
            'admin@trade.com' => [
                'username' => 'TradeCoDesk',
                'merchant_name' => 'TradeCoDesk',
                'completed_trades' => 5230,
                'completion_rate' => 99.70,
                'rating' => 4.95,
                'is_premier' => false,
            ],
            'user@trade.com' => [
                'username' => 'DemoTrader',
                'merchant_name' => 'DemoTrader',
                'completed_trades' => 320,
                'completion_rate' => 96.80,
                'rating' => 4.80,
                'is_premier' => false,
            ],
        ];

        foreach ($realUsers as $ru) {
            $stats = $realUserCustomStats[$ru->email] ?? null;
            if ($stats) {
                $ru->update([
                    'username' => $stats['username'],
                    'p2p_merchant_name' => $stats['merchant_name'],
                    'p2p_completed_trades' => $stats['completed_trades'],
                    'p2p_completion_rate' => $stats['completion_rate'],
                    'p2p_rating' => $stats['rating'],
                    'is_p2p_merchant' => true,
                    'is_dummy' => false,
                ]);
            } else {
                if (empty($ru->username)) {
                    $ru->username = User::generateUniqueUsername($ru->name ?: 'Trader');
                }
                $ru->p2p_completed_trades = rand(180, 2400);
                $ru->p2p_completion_rate = round(96.5 + (rand(0, 32) / 10), 1);
                $ru->p2p_rating = round(4.80 + (rand(0, 18) / 100), 2);
                $ru->is_dummy = false;
                $ru->save();
            }
        }

        // -------------------------------------------------------------
        // 2. CREATE TOP 3 PREMIER TRADERS (100% completion, 5/5 rating, is_top_trader = true)
        // -------------------------------------------------------------
        $michael = User::where('email', 'michaelkairithia@gmail.com')->first();
        $victor = User::where('email', 'vikigitonga14@gmail.com')->first();

        // Premier Trader 3 (ApexLiquidityKE)
        $apexTrader = User::updateOrCreate(
            ['email' => 'apex_top_trader@trade.com'],
            [
                'name' => 'Apex Liquidity KE',
                'username' => 'ApexLiquidityKE',
                'password' => Hash::make(Str::random(16)),
                'is_admin' => false,
                'is_p2p_merchant' => true,
                'is_dummy' => false, // Non-dummy so it's a premier partner
                'p2p_merchant_name' => 'ApexLiquidityKE',
                'p2p_completion_rate' => 100.00,
                'p2p_completed_trades' => 4890,
                'p2p_rating' => 5.00,
                'accepted_terms_at' => now()->subYears(1),
            ]
        );
        Wallet::updateOrCreate(
            ['user_id' => $apexTrader->id, 'currency' => 'USDT', 'is_demo' => false],
            ['available_balance' => 50000.00, 'locked_balance' => 0.00]
        );

        // Premier Trader 1: Michael (Mikeshi07) - Top Trader
        if ($michael) {
            // Sell ad (Buy USDT tab)
            P2PAd::updateOrCreate([
                'user_id' => $michael->id,
                'type' => 'sell',
                'fiat' => 'KES',
            ], [
                'asset' => 'USDT',
                'price' => 129.20,
                'total_amount' => 15000.00,
                'available_amount' => 14500.00,
                'min_limit' => 500.00,
                'max_limit' => 500000.00,
                'payment_methods' => ['mpesa', 'bank_transfer'],
                'terms' => '⭐ Official Top Desk · 100% Rating · Instant Safaricom M-Pesa release in < 2 mins.',
                'auto_reply' => 'Hello! Please send M-Pesa payment and click Payment Sent. Fast release guaranteed.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => true, // Top Trader #1
            ]);

            // Buy ad (Sell USDT tab)
            P2PAd::updateOrCreate([
                'user_id' => $michael->id,
                'type' => 'buy',
                'fiat' => 'KES',
            ], [
                'asset' => 'USDT',
                'price' => 129.00,
                'total_amount' => 12000.00,
                'available_amount' => 11200.00,
                'min_limit' => 500.00,
                'max_limit' => 450000.00,
                'payment_methods' => ['mpesa', 'bank_transfer'],
                'terms' => '⭐ Premier Desk Payout · Highest rate in Kenya · Immediate M-Pesa or Bank dispatch.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => true, // Top Trader #1
            ]);
        }

        // Premier Trader 2: Victor (VictorFX_254) - Top Trader
        if ($victor) {
            P2PAd::updateOrCreate([
                'user_id' => $victor->id,
                'type' => 'sell',
                'fiat' => 'KES',
            ], [
                'asset' => 'USDT',
                'price' => 129.35,
                'total_amount' => 10000.00,
                'available_amount' => 9600.00,
                'min_limit' => 1000.00,
                'max_limit' => 350000.00,
                'payment_methods' => ['mpesa', 'bank_transfer'],
                'terms' => '⭐ Verified Pro Trader · 100% Completion (6,150+ orders) · 24/7 fast release.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => true, // Top Trader #2
            ]);

            P2PAd::updateOrCreate([
                'user_id' => $victor->id,
                'type' => 'buy',
                'fiat' => 'KES',
            ], [
                'asset' => 'USDT',
                'price' => 128.85,
                'total_amount' => 8000.00,
                'available_amount' => 7500.00,
                'min_limit' => 1000.00,
                'max_limit' => 300000.00,
                'payment_methods' => ['mpesa', 'bank_transfer'],
                'terms' => '⭐ Direct instant payout to your M-Pesa number. Smooth and secure trade.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => true, // Top Trader #2
            ]);
        }

        // Premier Trader 3: ApexLiquidityKE - Top Trader
        P2PAd::updateOrCreate([
            'user_id' => $apexTrader->id,
            'type' => 'sell',
            'fiat' => 'KES',
        ], [
            'asset' => 'USDT',
            'price' => 129.50,
            'total_amount' => 25000.00,
            'available_amount' => 24100.00,
            'min_limit' => 2000.00,
            'max_limit' => 800000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ High Volume Institutional Desk · Instant M-Pesa & Bank Transfer.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => false,
            'is_limited' => false,
            'is_top_trader' => true, // Top Trader #3
        ]);

        P2PAd::updateOrCreate([
            'user_id' => $apexTrader->id,
            'type' => 'buy',
            'fiat' => 'KES',
        ], [
            'asset' => 'USDT',
            'price' => 128.70,
            'total_amount' => 20000.00,
            'available_amount' => 19200.00,
            'min_limit' => 2000.00,
            'max_limit' => 750000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ Apex Liquidity buy desk. Instant payment upon order creation.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => false,
            'is_limited' => false,
            'is_top_trader' => true, // Top Trader #3
        ]);

        // Remove previous duplicate ads from other real users to prevent clutter
        $otherRealUsers = User::where('is_dummy', false)
            ->whereNotIn('id', array_filter([$michael?->id, $victor?->id, $apexTrader->id]))
            ->get();

        foreach ($otherRealUsers as $oru) {
            // Give each non-premier real user at most 1 ad with is_top_trader = false
            P2PAd::where('user_id', $oru->id)->delete();
            P2PAd::create([
                'user_id' => $oru->id,
                'type' => ($oru->id % 2 === 0) ? 'sell' : 'buy',
                'asset' => 'USDT',
                'fiat' => 'KES',
                'price' => 130.20 + round(($oru->id % 5) * 0.15, 2),
                'total_amount' => 3000.00,
                'available_amount' => 2800.00,
                'min_limit' => 1000.00,
                'max_limit' => 150000.00,
                'payment_methods' => ['mpesa', 'bank_transfer'],
                'terms' => 'Standard verified peer trade. Fast response in chat.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => false, // NOT top trader
            ]);
        }

        // -------------------------------------------------------------
        // 3. 100+ DIVERSE DUMMY MERCHANTS (Trades up to 10k+, varied rates & prices)
        // -------------------------------------------------------------
        $merchants = [
            'NairobiEscrow', 'SwiftCryptoKE', 'SimbaExpress', 'KenyattaTraders',
            'KilimaniExchange', 'PataCrypto254', 'MombasaOTC', 'FastPay254', 'BlueStarTrading',
            'CryptoKingKe', 'SafariEscrow', 'BomaCapital', 'MaraOTC', 'ZamaniPay',
            'HabariCrypto', 'EldoretEx', 'NakuruP2P', 'RiftValEx', 'CoastLiquidity',
            'NyaliTraders', 'WestlandsOTC', 'KarenP2P', 'MuthaigaFX', 'KilifiTraders',
            'ThikaExpress', 'MachakosPay', 'KisumuOTC', 'DianiCrypto', 'VictoriaEscrow',
            'MaasaiPay', 'SerengetiFX', 'NgongLiquidity', 'UpperHillCapital', 'LavingtonEx',
            'ParklandsFX', 'EastleighOTC', 'GigiriTraders', 'KileleshwaCrypto', 'CBDExpress',
            'TwigaPay', 'KifaruOTC', 'NdovuTrading', 'ChuiCrypto', 'TaiExpress',
            'HarambeePay', 'JamhuriFX', 'ShujaaCrypto', 'AmaniEscrow', 'UhuruTraders',
            'BarakaOTC', 'FarajaFX', 'ZawadiCrypto', 'SubiraPay', 'ImaniEscrow',
            'UpendoTraders', 'NeemaFX', 'TumainiCrypto', 'SalamaPay', 'HodariOTC',
            'JasiriFX', 'KalamuCrypto', 'MwangazaPay', 'LuluTraders', 'DhahabuFX',
            'FedhaCrypto', 'MaliExpress', 'UtajiriPay', 'HekimaOTC', 'BusaraFX',
            'UwezoCrypto', 'BidiiTraders', 'UshindiFX', 'TajiCrypto', 'MwambaPay',
            'NgomeOTC', 'NguzoFX', 'ShinaCrypto', 'AsiliTraders', 'BustaniPay',
            'MtoFX', 'ZiwaCrypto', 'MlimaOTC', 'BondeFX', 'JuaTraders',
            'MweziPay', 'NyotaCrypto', 'AngaFX', 'UpepoOTC', 'MvuaPay',
            'RadiCrypto', 'NgurumoFX', 'KimbungaOTC', 'BahariPay', 'MwambaoFX',
            'PwaniCrypto', 'BaraTraders', 'KilelePay', 'KivuliFX', 'MwangaCrypto',
            'AfriCrypto254', 'KeringetFX', 'BometTrader', 'KerichoTeaEx', 'NaivashaOTC',
            'SamburuCapital', 'NanyukiEx', 'MeruFX', 'EmbuTrader', 'KituiPay',
            'GarissaOTC', 'WajirFX', 'ManderaCrypto', 'MarsabitEx', 'TurkanaPay'
        ];

        // Distributed trade volumes: from 185 up to 10,240 orders!
        $orderVolumeTiers = [
            185, 290, 420, 680, 890, 1140, 1420, 1850, 2340, 2890,
            3420, 3980, 4560, 5210, 5940, 6720, 7450, 8290, 9150, 9840, 10240
        ];

        $termsTemplates = [
            'M-Pesa payments only. No third party transfers. Phone name must match TradeCo account.',
            'Direct Bank Transfer (Equity, KCB, Co-op, NCBA). Instant release upon SMS alert.',
            'Online 24/7. Fast release guaranteed within 3 minutes of confirmed payment.',
            'Safaricom M-Pesa express paybill or send money. Please do not include crypto remarks.',
            'Verified merchant partner. Quick turnaround. Contact support if any delay.',
            'Strictly M-Pesa. Account age must exceed 30 days to trade with this merchant desk.',
        ];

        $count = 0;
        foreach ($merchants as $index => $name) {
            $num = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $email = "p2p_merchant_{$num}@trade.com";

            // Varied orders up to 10k+
            $completedTrades = $orderVolumeTiers[$index % count($orderVolumeTiers)] + rand(-25, 45);
            $completedTrades = max(120, $completedTrades);

            // Varied completion rates (96.5% - 99.8%)
            $completionRate = round(96.5 + (rand(0, 33) / 10), 1);
            $rating = round(4.80 + (rand(0, 18) / 100), 2);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $name,
                    'password' => Hash::make(Str::random(16)),
                    'is_admin' => false,
                    'is_moderator' => false,
                    'is_p2p_merchant' => true,
                    'is_dummy' => true,
                    'p2p_merchant_name' => $name,
                    'p2p_completion_rate' => $completionRate,
                    'p2p_completed_trades' => $completedTrades,
                    'p2p_rating' => $rating,
                    'accepted_terms_at' => now()->subMonths(rand(3, 24)),
                    'accepted_terms_ip' => '102.215.' . rand(10, 240) . '.' . rand(1, 254),
                ]
            );

            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 60000.00, 'locked_balance' => 0.00]
            );

            // Sell vs Buy distribution (60% sell, 40% buy)
            $type = ($index % 5 <= 2) ? 'sell' : 'buy';

            // 85% KES, 15% USD/USDT
            $isKes = ($index % 7 !== 0);
            $fiat = $isKes ? 'KES' : 'USDT';

            if ($fiat === 'KES') {
                if ($type === 'sell') {
                    // Buy USDT tab: prices range competitively from 129.80 to 135.20 KES
                    $price = 129.80 + round(($index * 0.06) + (rand(0, 15) / 100), 2);
                } else {
                    // Sell USDT tab: prices range competitively from 128.50 down to 124.80 KES
                    $offset = ($index % 40);
                    $price = max(124.00, round(128.50 - ($offset * 0.09) - (rand(0, 15) / 100), 2));
                }
            } else {
                $price = $type === 'sell' ? 1.00 + (rand(0, 3) / 100) : 0.99 - (rand(0, 2) / 100);
            }

            // Payment methods
            if ($index % 4 === 0) {
                $methods = ['bank_transfer'];
            } elseif ($index % 3 === 0) {
                $methods = ['mpesa', 'bank_transfer'];
            } else {
                $methods = ['mpesa'];
            }

            $totalAmount = rand(800, 35000);
            $availableAmount = round($totalAmount * (rand(85, 98) / 100), 2);

            $minLimit = $isKes ? (float) [500, 1000, 1500, 2000, 3000, 5000][rand(0, 5)] : (float) [10, 20, 50][rand(0, 2)];
            $maxLimit = $isKes ? (float) [35000, 75000, 150000, 300000, 600000, 1200000][rand(0, 5)] : (float) [500, 1500, 3500, 10000][rand(0, 3)];

            P2PAd::create([
                'user_id' => $user->id,
                'type' => $type,
                'asset' => 'USDT',
                'fiat' => $fiat,
                'price' => $price,
                'total_amount' => $totalAmount,
                'available_amount' => $availableAmount,
                'min_limit' => $minLimit,
                'max_limit' => $maxLimit,
                'payment_methods' => $methods,
                'payment_details' => [
                    'mpesa_number' => '+2547' . rand(10000000, 99999999),
                    'bank_name' => ['Equity Bank', 'KCB Bank', 'Co-operative Bank', 'NCBA'][rand(0, 3)],
                ],
                'terms' => $termsTemplates[rand(0, count($termsTemplates) - 1)],
                'auto_reply' => 'Trading window active. Please proceed with payment.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => true,
                'is_limited' => true,
                'is_top_trader' => false, // NOT top trader
                'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
            ]);

            $count++;
        }

        $this->command?->info("✓ Successfully configured unique usernames, 3 Top Traders (100% / 5.0 rating), and {$count} varied merchants (1k-10k+ orders).");
    }
}
