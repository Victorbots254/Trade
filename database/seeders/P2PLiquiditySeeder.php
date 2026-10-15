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

        // Trader 2: Elena_Vance_P2P (Top Trader #2)
        $apexTrader = User::updateOrCreate(
            ['email' => 'apex_top_trader@trade.com'],
            [
                'name' => 'Elena Vance',
                'username' => 'Elena_Vance_P2P',
                'password' => Hash::make(Str::random(16)),
                'is_admin' => false,
                'is_p2p_merchant' => true,
                'is_dummy' => true,
                'p2p_merchant_name' => 'Elena_Vance_P2P',
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
            'price' => 129.35,
            'total_amount' => 25000.00,
            'available_amount' => 24100.00,
            'min_limit' => 1000.00,
            'max_limit' => 750000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ Verified Global Merchant · 100% Rating · Instant M-Pesa & Bank Transfer.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => true,
            'is_limited' => true,
            'is_top_trader' => true,
            'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
        ]);
        P2PAd::updateOrCreate([
            'user_id' => $apexTrader->id,
            'type' => 'buy',
            'fiat' => 'KES',
        ], [
            'asset' => 'USDT',
            'price' => 128.85,
            'total_amount' => 20000.00,
            'available_amount' => 19500.00,
            'min_limit' => 1000.00,
            'max_limit' => 600000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ Verified High Liquidity Desk · 100% Rating · Fastest M-Pesa release.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => true,
            'is_limited' => true,
            'is_top_trader' => true,
            'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
        ]);

        // Trader 3: Tariq_CryptoDesk (Top Trader #3)
        $safariTrader = User::updateOrCreate(
            ['email' => 'safari_escrow_top@trade.com'],
            [
                'name' => 'Tariq Al-Mansoor',
                'username' => 'Tariq_CryptoDesk',
                'password' => Hash::make(Str::random(16)),
                'is_admin' => false,
                'is_p2p_merchant' => true,
                'is_dummy' => true,
                'p2p_merchant_name' => 'Tariq_CryptoDesk',
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
            'price' => 129.45,
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
        P2PAd::updateOrCreate([
            'user_id' => $safariTrader->id,
            'type' => 'buy',
            'fiat' => 'KES',
        ], [
            'asset' => 'USDT',
            'price' => 128.75,
            'total_amount' => 15000.00,
            'available_amount' => 14800.00,
            'min_limit' => 1000.00,
            'max_limit' => 500000.00,
            'payment_methods' => ['mpesa', 'bank_transfer'],
            'terms' => '⭐ Premier Instant Payouts · Direct M-Pesa & Equity / KCB Bank.',
            'time_limit_minutes' => 15,
            'status' => 'active',
            'is_dummy' => true,
            'is_limited' => true,
            'is_top_trader' => true,
            'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
        ]);

        // -------------------------------------------------------------
        // 3. 120+ REALISTIC MULTICULTURAL P2P TRADERS & MERCHANTS
        // -------------------------------------------------------------
        $merchants = [
            // African Traders & Desks (Kenya, Nigeria, Ghana, South Africa, Tanzania)
            'Brian_Otieno', 'Wanjiku_Cryptos', 'Chinedu_FX', 'Kwame_P2P', 'Amina_Hassan',
            'Dennis_Mwangi', 'Faith_Chebet', 'Adeyemi_Trades', 'Mercy_Wambui', 'Tunde_Crypto',
            'Kofi_Mensah', 'Zainab_Abdi', 'Kevin_Omondi', 'Fatima_Bello', 'David_Kimani',
            'Emeka_OTC', 'Grace_Njeri', 'Bakari_P2P', 'Sarah_Ochieng', 'Musa_Ibrahim',
            'Esther_Mutua', 'Obi_Express', 'Victor_Kiprop', 'Halima_Ali', 'Samuel_Ndungu',
            'Blessing_Okoro', 'Peter_Kamau', 'Ngozi_Fintech', 'Juma_Rashid', 'Kariuki_Trades',
            'Makena_Crypto', 'Oladipo_FX', 'Khadija_Trades', 'Mwangi_Desk', 'Chidi_Global',

            // European & Western Traders (UK, Germany, France, Scandinavia, US, Canada)
            'Alex_Vance', 'Marcus_Trader99', 'Elena_Vasiliev', 'Lucas_Miller', 'Sophie_Laurent',
            'Oliver_Wright', 'Daniel_Larsson', 'Hannah_Becker', 'Viktor_Novak', 'Chloe_Bennett',
            'Leo_Schneider', 'Emma_Watson_P2P', 'Noah_Davies', 'Sebastian_Koch', 'Clara_Dubois',
            'Julian_Bauer', 'Liam_OConnor', 'Mia_Andersson', 'Oscar_Lindholm', 'Zoe_Matthews',
            'Frederik_Berg', 'Astrid_Nielsen', 'Ethan_Clarke', 'Matteo_Rossi', 'Laura_Moretti',
            'Henrik_Vogel', 'Charlotte_P2P', 'Maximilian_FX', 'Ingrid_Trades', 'Gaston_Crypto',

            // Asian & Middle-Eastern Traders (UAE, Singapore, India, Japan, Korea, Hong Kong)
            'Tariq_Al_Mansoor', 'Chen_Wei_P2P', 'Rahul_Sharma_FX', 'Yuki_Tanaka', 'Priya_Patel',
            'Zhang_Hao', 'Farhan_Malik', 'Arjun_Reddy', 'Kenji_Sato', 'Ali_Reza_Exchange',
            'Sanjay_Kapoor', 'Mei_Ling_OTC', 'Rohan_Gupta', 'Hassan_Al_Sayed', 'Min_Jun_Kim',
            'Ananya_Roy', 'Tatsuya_Mori', 'Fahad_Al_Otaibi', 'Bao_Nguyen', 'Jin_Woo_Park',
            'Leila_Kashani', 'Vikram_Malhotra', 'Wei_Long_Trades', 'Kavita_P2P', 'Zubair_FX',

            // Latin American Traders (Brazil, Mexico, Argentina, Colombia, Spain)
            'Mateo_Silva', 'Camila_Reyes', 'Diego_Fernandez', 'Sofia_Gomez_OTC', 'Thiago_Santos',
            'Valentina_Cruz', 'Carlos_Mendoza', 'Isabella_Torres', 'Lucas_Oliveira', 'Lucia_Navarro',
            'Gabriel_Costa', 'Mariana_Alvarez', 'Alejandro_Ruiz', 'Fernanda_Lima', 'Javier_Morales',
            'Andres_Castillo', 'Bianca_Rocha', 'Emilio_Vargas', 'Catalina_P2P', 'Rodrigo_Silva',

            // Verified Merchant & Desk Style Handles
            'CryptoWhale_Prime', 'AlphaTrader_Desk', 'GlobalCash_Pro', 'FastTrack_OTC', 'Titan_Merchant',
            'Velox_Trades', 'Nexus_CryptoFlow', 'SilverStone_P2P', 'Zenith_Trader', 'IronClad_P2P',
            'Nordic_Liquidity', 'Apex_Capital_P2P', 'Vanguard_Desk', 'BlueHorizon_FX', 'SwiftSettler',
            'Beacon_Trader', 'Anchor_Liquidity', 'Equinox_P2P', 'Pioneer_Trading', 'Sterling_Escrow'
        ];

        // Distributed trade volumes: from 185 up to 10,240 orders!
        $orderVolumeTiers = [
            185, 290, 420, 680, 890, 1140, 1420, 1850, 2340, 2890,
            3420, 3980, 4560, 5210, 5940, 6720, 7450, 8290, 9150, 9840, 10240
        ];

        $termsTemplates = [
            'Instant M-Pesa release. Please ensure payment name matches your verified TradeCo profile.',
            'Fast release in < 3 minutes. Direct M-Pesa or Bank Transfer (Equity, KCB, Standard Chartered).',
            'Online 24/7. Verified institutional desk. Zero fees, immediate escrow confirmation.',
            'Safaricom M-Pesa express send money or Paybill. Please leave reference field blank / order ID only.',
            'Direct bank wire or instant M-Pesa. High liquidity available for fast turnaround.',
            'Premier peer desk. Friendly & professional. Escrow auto-release upon payment confirmation.',
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

            $nameClean = str_replace('_', ' ', $name);
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $nameClean,
                    'username' => $targetUsername,
                    'password' => Hash::make(Str::random(16)),
                    'is_admin' => false,
                    'is_moderator' => false,
                    'is_p2p_merchant' => true,
                    'is_dummy' => true,
                    'p2p_merchant_name' => $targetUsername,
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

            // Sell vs Buy distribution (55% sell, 45% buy)
            $type = ($index % 2 === 0) ? 'sell' : 'buy';
            $fiat = 'KES'; // 100% integrated Kenyan Shillings for all merchants

            if ($type === 'sell') {
                // Buy USDT tab: prices range competitively from 129.50 up to 134.80 KES
                // Step by ~0.05 KES + randomized fraction so every merchant has a distinct, competitive price
                $price = 129.50 + round(($index * 0.05) + (rand(1, 9) / 100), 2);
            } else {
                // Sell USDT tab: prices range competitively from 128.65 down to 124.50 KES
                // Sorted descending: highest payout first
                $price = max(124.50, round(128.65 - (($index / 2) * 0.06) - (rand(1, 9) / 100), 2));
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

            $minLimit = (float) [500, 1000, 1500, 2000, 3000, 5000][rand(0, 5)];
            $maxLimit = (float) [35000, 75000, 150000, 300000, 600000, 1200000][rand(0, 5)];

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
                    'bank_name' => ['Equity Bank', 'KCB Bank', 'Co-operative Bank', 'NCBA', 'Absa Kenya', 'Standard Chartered'][rand(0, 5)],
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

            // For every 6th merchant, also create a secondary USD ad with DIVERSE dynamic pricing (NOT standard 1:1)
            if ($index % 6 === 0) {
                $usdType = ($index % 12 === 0) ? 'sell' : 'buy';
                $usdPrice = ($usdType === 'sell')
                    ? round(1.002 + (($index % 20) * 0.001) + (rand(1, 4) / 1000), 3)
                    : round(0.998 - (($index % 20) * 0.001) - (rand(1, 4) / 1000), 3);

                P2PAd::create([
                    'user_id' => $user->id,
                    'type' => $usdType,
                    'asset' => 'USDT',
                    'fiat' => 'USDT',
                    'price' => $usdPrice,
                    'total_amount' => rand(500, 15000),
                    'available_amount' => rand(450, 14200),
                    'min_limit' => 20.00,
                    'max_limit' => 5000.00,
                    'payment_methods' => ['bank_transfer'],
                    'payment_details' => [
                        'bank_name' => 'International Wire / SWIFT',
                    ],
                    'terms' => 'Direct international wire / SEPA transfer. Verified accounts only.',
                    'time_limit_minutes' => 30,
                    'status' => 'active',
                    'is_dummy' => true,
                    'is_limited' => true,
                    'is_top_trader' => false,
                    'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
                ]);
            }

            $count++;
        }

        $this->command?->info("✓ Successfully configured unique usernames, 3 Top Traders (100% / 5.0 rating), and {$count} varied merchants (1k-10k+ orders).");
    }
}
