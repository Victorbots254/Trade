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
     * Run the database seeds for 100 dummy P2P merchants and active ads.
     */
    public function run(bool $clearDummy = true): void
    {
        if ($clearDummy) {
            $this->command?->info('Clearing old dummy P2P ads and users...');
            P2PAd::where('is_dummy', true)->delete();
            User::where('is_dummy', true)->delete();
        }

        // 1. Ensure real user ads exist and are marked as real (is_dummy = false, is_limited = false)
        $realUsers = User::where('is_dummy', false)
            ->where(function ($q) {
                $q->where('is_admin', true)
                  ->orWhere('email', 'like', '%michael%')
                  ->orWhere('email', 'like', '%viki%')
                  ->orWhere('email', 'user@trade.com');
            })
            ->get();

        if ($realUsers->isEmpty()) {
            $realUsers = User::where('is_dummy', false)->take(3)->get();
        }

        foreach ($realUsers as $realUser) {
            $realUser->update([
                'is_p2p_merchant' => true,
                'p2p_merchant_name' => $realUser->p2p_merchant_name ?: $realUser->name,
                'p2p_completion_rate' => 99.8,
                'p2p_completed_trades' => max(150, (int) $realUser->p2p_completed_trades),
                'is_dummy' => false,
            ]);

            // Top Buy USDT ad (Merchant sells USDT to user)
            P2PAd::updateOrCreate([
                'user_id' => $realUser->id,
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
                'payment_details' => ['phone' => '+254700112233', 'name' => $realUser->name],
                'terms' => '⭐ Verified Direct Partner · 0% fee · Instant M-Pesa release in under 2 minutes.',
                'auto_reply' => 'Hello! Please send M-Pesa payment to +254700112233 and click Payment Sent.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'requirement_text' => null,
            ]);

            // Top Sell USDT ad (Merchant buys USDT from user)
            P2PAd::updateOrCreate([
                'user_id' => $realUser->id,
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
                'payment_details' => ['phone' => '+254700112233', 'name' => $realUser->name],
                'terms' => '⭐ Instant payout to your M-Pesa or Bank once order is initiated.',
                'auto_reply' => 'Order received! Sending M-Pesa funds right now.',
                'time_limit_minutes' => 15,
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
                'requirement_text' => null,
            ]);

            // Top USD/USDT stablecoin ad
            P2PAd::updateOrCreate([
                'user_id' => $realUser->id,
                'type' => 'sell',
                'fiat' => 'USDT',
            ], [
                'asset' => 'USDT',
                'price' => 1.00,
                'total_amount' => 20000.00,
                'available_amount' => 19500.00,
                'min_limit' => 10.00,
                'max_limit' => 5000.00,
                'payment_methods' => ['bank_transfer', 'mpesa'],
                'terms' => '⭐ 1:1 USD Stablecoin Direct Trade · Zero fees.',
                'status' => 'active',
                'is_dummy' => false,
                'is_limited' => false,
            ]);
        }

        // 2. 100 Authentic P2P Merchant Profiles
        $merchants = [
            'ApexLiquidityKE', 'NairobiEscrow', 'SwiftCryptoKE', 'SimbaExpress', 'KenyattaTraders',
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
            'PwaniCrypto', 'BaraTraders', 'KilelePay', 'KivuliFX', 'MwangaCrypto'
        ];

        $termsTemplates = [
            'M-Pesa payments only. No third party transfers. Phone name must match TradeCo account.',
            'Direct Bank Transfer (Equity, KCB, Co-op, NCBA). Instant release upon SMS alert.',
            'Online 24/7. Fast release guaranteed within 3 minutes of confirmed payment.',
            'Safaricom M-Pesa express paybill or send money. Please do not include crypto remarks.',
            'Verified merchant partner. Quick escrow turnaround. Contact support if any delay.',
            'Strictly M-Pesa. Account age must exceed 30 days to trade with this merchant desk.',
        ];

        $count = 0;
        foreach ($merchants as $index => $name) {
            $num = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $email = "p2p_merchant_{$num}@trade.com";

            $completedTrades = rand(65, 3850);
            $completionRate = round(96.5 + (rand(0, 35) / 10), 1); // 96.5% to 100.0%

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make(Str::random(16)),
                    'is_admin' => false,
                    'is_moderator' => false,
                    'is_p2p_merchant' => true,
                    'is_dummy' => true,
                    'p2p_merchant_name' => $name,
                    'p2p_completion_rate' => min(100.0, $completionRate),
                    'p2p_completed_trades' => $completedTrades,
                    'accepted_terms_at' => now()->subMonths(rand(3, 18)),
                    'accepted_terms_ip' => '102.215.' . rand(10, 240) . '.' . rand(1, 254),
                ]
            );

            // Give dummy user sufficient wallet balance to back ads
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 50000.00, 'locked_balance' => 0.00]
            );

            // Determine if ad is for 'sell' (Buy USDT tab) or 'buy' (Sell USDT tab)
            // First 55 merchants provide 'sell' ads, remaining 45 provide 'buy' ads
            $type = ($index < 55) ? 'sell' : 'buy';

            // Determine currency: 85% KES, 15% USD/USDT
            $isKes = ($index % 7 !== 0);
            $fiat = $isKes ? 'KES' : 'USDT';

            // Determine price with realistic spread
            if ($fiat === 'KES') {
                if ($type === 'sell') {
                    // Buy USDT tab: prices range competitively from 129.50 to 134.80 KES
                    $price = 129.50 + round(($index * 0.09) + (rand(0, 20) / 100), 2);
                } else {
                    // Sell USDT tab: prices range competitively from 128.80 down to 125.20 KES
                    $offset = ($index - 55);
                    $price = max(124.50, round(128.80 - ($offset * 0.08) - (rand(0, 15) / 100), 2));
                }
            } else {
                // USD/USDT prices
                $price = $type === 'sell' ? 1.00 + (rand(0, 3) / 100) : 0.99 - (rand(0, 2) / 100);
            }

            // Payment methods
            if ($index % 5 === 0) {
                $methods = ['bank_transfer'];
            } elseif ($index % 3 === 0) {
                $methods = ['mpesa', 'bank_transfer'];
            } else {
                $methods = ['mpesa'];
            }

            $totalAmount = rand(500, 25000);
            $availableAmount = round($totalAmount * (rand(85, 98) / 100), 2);

            $minLimit = $isKes ? (float) [500, 1000, 1500, 2000, 3000, 5000][rand(0, 5)] : (float) [10, 20, 50][rand(0, 2)];
            $maxLimit = $isKes ? (float) [30000, 50000, 80000, 150000, 350000, 750000, 1200000][rand(0, 6)] : (float) [500, 1500, 3000, 10000][rand(0, 3)];

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
                'requirement_text' => 'Requires 30+ completed P2P orders and account age > 30 days.',
            ]);

            $count++;
        }

        $this->command?->info("✓ Successfully created {$count} realistic P2P merchants and active ads with diverse price tiers.");
    }
}
