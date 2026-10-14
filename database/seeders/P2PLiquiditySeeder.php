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

        // 1. Keep authorized real merchant (JamesKe / michaelkairithia3@gmail.com)
        User::where('username', 'JamesKE')->where('email', '!=', 'michaelkairithia3@gmail.com')->update(['username' => 'Trader13_KE']);
        User::where('username', 'JamesKe')->where('email', '!=', 'michaelkairithia3@gmail.com')->update(['username' => 'Trader13_James']);

        $james = User::where('email', 'michaelkairithia3@gmail.com')->first();
        if ($james) {
            $james->update([
                'username' => 'JamesKe',
                'p2p_merchant_name' => 'JamesKe',
                'p2p_completed_trades' => 8420,
                'p2p_completion_rate' => 100.00,
                'p2p_rating' => 5.00,
                'is_p2p_merchant' => true,
                'is_dummy' => false,
                'p2p_payment_details' => [
                    'mpesa' => [
                        'type' => 'phone',
                        'phone' => '+254712345678',
                        'account_name' => 'James Ke',
                    ],
                    'bank' => [
                        'bank_name' => 'Equity Bank',
                        'account_number' => '0110192837465',
                        'account_name' => 'James Ke',
                    ],
                ],
            ]);
        }

        // Set is_p2p_merchant = false for all other real users
        User::where('is_dummy', false)
            ->where('email', '!=', 'michaelkairithia3@gmail.com')
            ->get()
            ->each(function ($u) {
                if (empty($u->username) || $u->username === 'Mikeshi07') {
                    $u->username = $u->username === 'Mikeshi07' ? 'MichaelK_KE' : User::generateUniqueUsername($u->name ?: 'Trader');
                }
                $u->is_p2p_merchant = false;
                $u->save();
            });

        // Remove any ads belonging to users who are not approved merchants
        P2PAd::whereIn('user_id', User::where('is_p2p_merchant', false)->pluck('id'))->delete();

        // 2. The 3 Premier Top Traders (100% completion, 5/5 rating, is_top_trader = true)
        // Trader 1: JamesKe (michaelkairithia3@gmail.com)
        if ($james) {
            P2PAd::updateOrCreate([
                'user_id' => $james->id,
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
                'terms' => '⭐ Verified Premier Desk · 100% Rating · Instant Safaricom M-Pesa release in < 2 mins.',
                'auto_reply' => 'Hello! Please send M-Pesa payment and click Payment Sent. Fast release guaranteed.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => true,
            ]);

            P2PAd::updateOrCreate([
                'user_id' => $james->id,
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
                'terms' => '⭐ Top Desk Payout · Highest rate in Kenya · Immediate M-Pesa or Bank dispatch.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'is_top_trader' => true,
            ]);
        }

        // Trader 2: ApexLiquidityKE (Top Trader #2)
        $apexTrader = User::updateOrCreate(
            ['email' => 'apex_top_trader@trade.com'],
            [
                'name' => 'Apex Liquidity KE',
                'username' => 'ApexLiquidityKE',
                'password' => Hash::make(Str::random(16)),
                'is_admin' => false,
                'is_p2p_merchant' => true,
                'is_dummy' => true,
                'p2p_merchant_name' => 'ApexLiquidityKE',
                'p2p_completion_rate' => 100.00,
                'p2p_completed_trades' => 6150,
                'p2p_rating' => 5.00,
            ]
        );
        P2PAd::updateOrCreate([
            'user_id' => $apexTrader->id,
            'type' => 'sell',
            'fiat' => 'KES',
        ], [
            'asset' => 'USDT',
            'price' => 129.40,
            'total_amount' => 25000.00,
            'available_amount' => 24100.00,
            'min_limit' => 1500.00,
            'max_limit' => 750000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ High Volume Institutional Desk · Instant M-Pesa & Bank Transfer.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => true,
            'is_limited' => true,
            'is_top_trader' => true,
            'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
        ]);

        // Trader 3: SafariEscrow (Top Trader #3)
        $safariTrader = User::updateOrCreate(
            ['email' => 'safari_escrow_top@trade.com'],
            [
                'name' => 'Safari Escrow',
                'username' => 'SafariEscrow',
                'password' => Hash::make(Str::random(16)),
                'is_admin' => false,
                'is_p2p_merchant' => true,
                'is_dummy' => true,
                'p2p_merchant_name' => 'SafariEscrow',
                'p2p_completion_rate' => 100.00,
                'p2p_completed_trades' => 4890,
                'p2p_rating' => 5.00,
            ]
        );
        P2PAd::updateOrCreate([
            'user_id' => $safariTrader->id,
            'type' => 'sell',
            'fiat' => 'KES',
        ], [
            'asset' => 'USDT',
            'price' => 129.55,
            'total_amount' => 18000.00,
            'available_amount' => 17400.00,
            'min_limit' => 1000.00,
            'max_limit' => 500000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ Verified 24/7 Escrow Partner · 100% Rating · Swift M-Pesa & Bank release.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => true,
            'is_limited' => true,
            'is_top_trader' => true,
            'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
        ]);

        // -------------------------------------------------------------
        // 3. 100+ DIVERSE DUMMY MERCHANTS (Trades up to 10k+, varied rates & prices)
        // -------------------------------------------------------------
        $merchants = [
            'NairobiEscrow', 'SwiftCryptoKE', 'SimbaExpress', 'KenyattaTraders',
            'KilimaniExchange', 'PataCrypto254', 'MombasaOTC', 'FastPay254', 'BlueStarTrading',
            'CryptoKingKe', 'SavannahEscrow', 'BomaCapital', 'MaraOTC', 'ZamaniPay',
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

            $targetUsername = $name;
            if (User::where('username', $targetUsername)->where('email', '!=', $email)->exists()) {
                $targetUsername = $name . ($index + 1);
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $targetUsername,
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
