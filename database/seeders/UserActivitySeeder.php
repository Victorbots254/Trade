<?php

namespace Database\Seeders;

use App\Models\BinaryOptionContract;
use App\Models\Deposit;
use App\Models\Market;
use App\Models\MmfInterestLog;
use App\Models\MmfSubscription;
use App\Models\Order;
use App\Models\P2PAd;
use App\Models\P2POrder;
use App\Models\Trade;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param User|null $targetUser
     * @param bool $clearExisting
     */
    public function run(?User $targetUser = null, bool $clearExisting = false): void
    {
        $users = $targetUser ? collect([$targetUser]) : User::all();

        if ($users->isEmpty()) {
            $this->command?->warn('No users found in database to seed activity.');
            return;
        }

        // Ensure primary active markets exist
        $btc = Market::firstOrCreate(['symbol' => 'BTC/USDT'], [
            'base_currency' => 'BTC', 'quote_currency' => 'USDT', 'last_price' => 83250.00,
            'change_24h' => 2.45, 'high_24h' => 84400.00, 'low_24h' => 82100.00, 'volume_24h' => 245.80,
            'min_order_size' => 0.0001, 'price_precision' => 2, 'quantity_precision' => 4,
        ]);
        $eth = Market::firstOrCreate(['symbol' => 'ETH/USDT'], [
            'base_currency' => 'ETH', 'quote_currency' => 'USDT', 'last_price' => 2540.00,
            'change_24h' => -1.15, 'high_24h' => 2590.00, 'low_24h' => 2510.00, 'volume_24h' => 1840.50,
            'min_order_size' => 0.001, 'price_precision' => 2, 'quantity_precision' => 4,
        ]);
        $sol = Market::firstOrCreate(['symbol' => 'SOL/USDT'], [
            'base_currency' => 'SOL', 'quote_currency' => 'USDT', 'last_price' => 148.50,
            'change_24h' => 5.20, 'high_24h' => 152.00, 'low_24h' => 142.50, 'volume_24h' => 12400.00,
            'min_order_size' => 0.01, 'price_precision' => 2, 'quantity_precision' => 4,
        ]);
        $gold = Market::firstOrCreate(['symbol' => 'GOLD/USDT'], [
            'base_currency' => 'GOLD', 'quote_currency' => 'USDT', 'last_price' => 2512.40,
            'change_24h' => 1.12, 'high_24h' => 2525.00, 'low_24h' => 2495.00, 'volume_24h' => 4800.00,
            'min_order_size' => 0.01, 'price_precision' => 2, 'quantity_precision' => 4,
        ]);
        $nvda = Market::firstOrCreate(['symbol' => 'NVDA/USDT'], [
            'base_currency' => 'NVDA', 'quote_currency' => 'USDT', 'last_price' => 128.50,
            'change_24h' => 4.25, 'high_24h' => 131.00, 'low_24h' => 124.80, 'volume_24h' => 45000.00,
            'min_order_size' => 0.1, 'price_precision' => 2, 'quantity_precision' => 2,
        ]);

        // Secondary counterparty user for peer trades/P2P
        $counterparty = User::where('id', '!=', $users->first()->id)->first() ?? User::first();

        foreach ($users as $user) {
            $this->command?->info("Seeding realistic transaction history for: {$user->name} ({$user->email})...");

            if (empty($user->bep20_address)) {
                $user->update([
                    'bep20_address' => '0x' . Str::random(40),
                    'p2p_merchant_name' => $user->name,
                    'p2p_completion_rate' => 99.2,
                    'p2p_completed_trades' => 24,
                ]);
            }

            if ($clearExisting) {
                BinaryOptionContract::where('user_id', $user->id)->delete();
                Withdrawal::where('user_id', $user->id)->delete();
                Deposit::where('user_id', $user->id)->delete();
                Order::where('user_id', $user->id)->delete();
                MmfSubscription::where('user_id', $user->id)->delete();
                MmfInterestLog::where('user_id', $user->id)->delete();
            }

            // -------------------------------------------------------------
            // 1. WALLET BALANCES (Realistic live and demo portfolios)
            // -------------------------------------------------------------
            // Live Wallets
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 3450.00, 'locked_balance' => 200.00]
            );
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'BTC', 'is_demo' => false],
                ['available_balance' => 0.0450, 'locked_balance' => 0.00]
            );
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'ETH', 'is_demo' => false],
                ['available_balance' => 0.6500, 'locked_balance' => 0.00]
            );
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'SOL', 'is_demo' => false],
                ['available_balance' => 4.5000, 'locked_balance' => 0.00]
            );
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'GOLD', 'is_demo' => false],
                ['available_balance' => 0.8500, 'locked_balance' => 0.00]
            );

            // Demo Wallet
            Wallet::updateOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => true],
                ['available_balance' => 10000.00, 'locked_balance' => 0.00]
            );
            $user->update(['demo_balance' => 10000.00]);

            // -------------------------------------------------------------
            // 2. DEPOSITS HISTORY (Crypto BEP20, M-Pesa, Pending)
            // -------------------------------------------------------------
            // Deposit 1: Approved BEP20 USDT (2 weeks ago)
            Deposit::create([
                'user_id' => $user->id,
                'payment_method' => 'crypto',
                'currency' => 'USDT',
                'amount' => 2500.00,
                'kes_amount' => 325000.00,
                'exchange_rate' => 130.00,
                'tx_hash' => '0x' . strtolower(Str::random(64)),
                'status' => 'approved',
                'approved_at' => Carbon::now()->subDays(14)->addMinutes(15),
                'created_at' => Carbon::now()->subDays(14),
                'updated_at' => Carbon::now()->subDays(14)->addMinutes(15),
            ]);

            // Deposit 2: Approved M-Pesa KES (6 days ago)
            Deposit::create([
                'user_id' => $user->id,
                'payment_method' => 'mpesa',
                'phone_number' => '+254712345678',
                'currency' => 'USDT',
                'amount' => 500.00,
                'kes_amount' => 65000.00,
                'exchange_rate' => 130.00,
                'mpesa_receipt' => 'SH' . strtoupper(Str::random(8)),
                'status' => 'approved',
                'approved_at' => Carbon::now()->subDays(6)->addMinutes(5),
                'created_at' => Carbon::now()->subDays(6),
                'updated_at' => Carbon::now()->subDays(6)->addMinutes(5),
            ]);

            // Deposit 3: Pending Deposit (1 hour ago - ready for admin to test approval)
            Deposit::create([
                'user_id' => $user->id,
                'payment_method' => 'crypto',
                'currency' => 'USDT',
                'amount' => 750.00,
                'kes_amount' => 97500.00,
                'exchange_rate' => 130.00,
                'tx_hash' => '0x' . strtolower(Str::random(64)),
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(1),
                'updated_at' => Carbon::now()->subHours(1),
            ]);

            // -------------------------------------------------------------
            // 3. WITHDRAWALS HISTORY (Completed with Date Paid, Rejected, Pending)
            // -------------------------------------------------------------
            // Withdrawal 1: Approved & Paid Out with TxHash & Date Paid (4 days ago)
            Withdrawal::create([
                'user_id' => $user->id,
                'currency' => 'USDT',
                'amount' => 300.00,
                'bep20_address' => $user->bep20_address ?: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F',
                'tx_hash' => '0x' . strtolower(Str::random(64)),
                'status' => 'approved',
                'admin_notes' => 'Dispatched via Binance Hot Wallet',
                'approved_at' => Carbon::now()->subDays(4)->addMinutes(20),
                'paid_at' => Carbon::now()->subDays(4)->addMinutes(25),
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4)->addMinutes(25),
            ]);

            // Withdrawal 2: Rejected with Admin Note (2 days ago)
            Withdrawal::create([
                'user_id' => $user->id,
                'currency' => 'USDT',
                'amount' => 150.00,
                'bep20_address' => '0x3333333333333333333333333333333333333333',
                'status' => 'rejected',
                'admin_notes' => 'Contract address detected. Funds refunded to wallet.',
                'approved_at' => Carbon::now()->subDays(2)->addMinutes(10),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2)->addMinutes(10),
            ]);

            // Withdrawal 3: Pending Withdrawal (Ready for admin to test review & approval modal)
            Withdrawal::create([
                'user_id' => $user->id,
                'currency' => 'USDT',
                'amount' => 450.00,
                'bep20_address' => $user->bep20_address ?: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F',
                'status' => 'pending',
                'created_at' => Carbon::now()->subMinutes(35),
                'updated_at' => Carbon::now()->subMinutes(35),
            ]);

            // -------------------------------------------------------------
            // 4. QUICK OPTIONS CONTRACTS (Binary Options Wins & Losses)
            // -------------------------------------------------------------
            $binaryOptionsData = [
                [
                    'market_id' => $btc->id, 'direction' => 'higher', 'entry_price' => 82140.00,
                    'strike_price' => 82350.00, 'investment_amount' => 100.00, 'payout_rate' => 0.88,
                    'payout_amount' => 188.00, 'duration_seconds' => 60, 'status' => 'win',
                    'is_demo' => false, 'days_ago' => 5,
                ],
                [
                    'market_id' => $eth->id, 'direction' => 'lower', 'entry_price' => 2580.00,
                    'strike_price' => 2552.00, 'investment_amount' => 50.00, 'payout_rate' => 0.88,
                    'payout_amount' => 94.00, 'duration_seconds' => 300, 'status' => 'win',
                    'is_demo' => false, 'days_ago' => 4,
                ],
                [
                    'market_id' => $sol->id, 'direction' => 'higher', 'entry_price' => 149.20,
                    'strike_price' => 148.40, 'investment_amount' => 75.00, 'payout_rate' => 0.88,
                    'payout_amount' => 0.00, 'duration_seconds' => 60, 'status' => 'loss',
                    'is_demo' => false, 'days_ago' => 3,
                ],
                [
                    'market_id' => $gold->id, 'direction' => 'higher', 'entry_price' => 2505.50,
                    'strike_price' => 2514.80, 'investment_amount' => 200.00, 'payout_rate' => 0.88,
                    'payout_amount' => 376.00, 'duration_seconds' => 900, 'status' => 'win',
                    'is_demo' => false, 'days_ago' => 2,
                ],
                [
                    'market_id' => $nvda->id, 'direction' => 'lower', 'entry_price' => 131.50,
                    'strike_price' => 132.80, 'investment_amount' => 60.00, 'payout_rate' => 0.88,
                    'payout_amount' => 0.00, 'duration_seconds' => 300, 'status' => 'loss',
                    'is_demo' => false, 'days_ago' => 1,
                ],
                [
                    'market_id' => $btc->id, 'direction' => 'higher', 'entry_price' => 83100.00,
                    'strike_price' => 83320.00, 'investment_amount' => 150.00, 'payout_rate' => 0.88,
                    'payout_amount' => 282.00, 'duration_seconds' => 60, 'status' => 'win',
                    'is_demo' => false, 'days_ago' => 0.2, // ~5 hours ago
                ],
                // Demo contracts
                [
                    'market_id' => $btc->id, 'direction' => 'higher', 'entry_price' => 82800.00,
                    'strike_price' => 83050.00, 'investment_amount' => 500.00, 'payout_rate' => 0.88,
                    'payout_amount' => 940.00, 'duration_seconds' => 60, 'status' => 'win',
                    'is_demo' => true, 'days_ago' => 1,
                ],
                [
                    'market_id' => $sol->id, 'direction' => 'lower', 'entry_price' => 146.00,
                    'strike_price' => 147.20, 'investment_amount' => 250.00, 'payout_rate' => 0.88,
                    'payout_amount' => 0.00, 'duration_seconds' => 300, 'status' => 'loss',
                    'is_demo' => true, 'days_ago' => 0.5,
                ],
            ];

            foreach ($binaryOptionsData as $bo) {
                $createdAt = Carbon::now()->subHours((int) ($bo['days_ago'] * 24));
                $expiresAt = (clone $createdAt)->addSeconds($bo['duration_seconds']);

                BinaryOptionContract::create([
                    'user_id' => $user->id,
                    'market_id' => $bo['market_id'],
                    'direction' => $bo['direction'],
                    'entry_price' => $bo['entry_price'],
                    'strike_price' => $bo['strike_price'],
                    'investment_amount' => $bo['investment_amount'],
                    'payout_rate' => $bo['payout_rate'],
                    'payout_amount' => $bo['payout_amount'],
                    'duration_seconds' => $bo['duration_seconds'],
                    'status' => $bo['status'],
                    'is_demo' => $bo['is_demo'],
                    'expires_at' => $expiresAt,
                    'created_at' => $createdAt,
                    'updated_at' => $expiresAt,
                ]);
            }

            // -------------------------------------------------------------
            // 5. SPOT ORDERS & TRADES (Open & Filled History)
            // -------------------------------------------------------------
            // Open Limit Orders (shows under Open Orders tab)
            Order::create([
                'user_id' => $user->id,
                'market_id' => $btc->id,
                'side' => 'buy',
                'type' => 'limit',
                'price' => 81500.00,
                'quantity' => 0.0250,
                'filled_quantity' => 0.0000,
                'status' => 'open',
                'is_demo' => false,
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ]);

            Order::create([
                'user_id' => $user->id,
                'market_id' => $eth->id,
                'side' => 'sell',
                'type' => 'limit',
                'price' => 2750.00,
                'quantity' => 0.2000,
                'filled_quantity' => 0.0000,
                'status' => 'open',
                'is_demo' => false,
                'created_at' => Carbon::now()->subHours(4),
                'updated_at' => Carbon::now()->subHours(4),
            ]);

            // Filled Orders (shows in Order History)
            $filledOrders = [
                ['market' => $btc, 'side' => 'buy', 'price' => 82100.00, 'qty' => 0.0450, 'days_ago' => 7],
                ['market' => $eth, 'side' => 'buy', 'price' => 2480.00, 'qty' => 0.6500, 'days_ago' => 6],
                ['market' => $sol, 'side' => 'buy', 'price' => 141.20, 'qty' => 4.5000, 'days_ago' => 5],
                ['market' => $gold, 'side' => 'buy', 'price' => 2485.00, 'qty' => 0.8500, 'days_ago' => 4],
                ['market' => $btc, 'side' => 'sell', 'price' => 84200.00, 'qty' => 0.0150, 'days_ago' => 2], // Took partial profit
            ];

            foreach ($filledOrders as $fo) {
                $time = Carbon::now()->subDays($fo['days_ago']);
                $order = Order::create([
                    'user_id' => $user->id,
                    'market_id' => $fo['market']['id'],
                    'side' => $fo['side'],
                    'type' => 'limit',
                    'price' => $fo['price'],
                    'quantity' => $fo['qty'],
                    'filled_quantity' => $fo['qty'],
                    'status' => 'filled',
                    'is_demo' => false,
                    'created_at' => $time,
                    'updated_at' => $time->copy()->addMinutes(2),
                ]);

                Trade::create([
                    'market_id' => $fo['market']['id'],
                    'buy_order_id' => $fo['side'] === 'buy' ? $order->id : 1,
                    'sell_order_id' => $fo['side'] === 'sell' ? $order->id : 1,
                    'buyer_id' => $fo['side'] === 'buy' ? $user->id : $counterparty->id,
                    'seller_id' => $fo['side'] === 'sell' ? $user->id : $counterparty->id,
                    'price' => $fo['price'],
                    'quantity' => $fo['qty'],
                    'side' => $fo['side'],
                    'created_at' => $time->copy()->addMinutes(2),
                ]);
            }

            // -------------------------------------------------------------
            // 6. P2P ORDERS (Completed Buy & Sell Orders)
            // -------------------------------------------------------------
            try {
                $p2pAd = P2PAd::firstOrCreate([
                    'user_id' => $counterparty->id,
                    'type' => 'sell',
                    'asset' => 'USDT',
                    'fiat' => 'KES',
                ], [
                    'price' => 131.50,
                    'total_amount' => 5000.00,
                    'available_amount' => 4650.00,
                    'min_limit' => 1000.00,
                    'max_limit' => 300000.00,
                    'payment_methods' => ['M-Pesa', 'Bank Transfer'],
                    'payment_details' => ['phone' => '+254700000000', 'name' => 'Verified Liquidity Partner'],
                    'terms' => 'Instant release within 5 mins.',
                    'time_limit_minutes' => 15,
                    'status' => 'active',
                ]);

                // Completed P2P Buy Order
                P2POrder::create([
                    'order_number' => 'P2P' . strtoupper(Str::random(10)),
                    'ad_id' => $p2pAd->id,
                    'buyer_id' => $user->id,
                    'seller_id' => $counterparty->id,
                    'crypto_amount' => 200.00,
                    'escrow_fee' => 0.00,
                    'fiat_amount' => 26300.00,
                    'price' => 131.50,
                    'payment_method' => 'M-Pesa',
                    'payment_details' => ['phone' => '+254712345678'],
                    'status' => 'completed',
                    'paid_at' => Carbon::now()->subDays(8)->addMinutes(4),
                    'completed_at' => Carbon::now()->subDays(8)->addMinutes(8),
                    'created_at' => Carbon::now()->subDays(8),
                    'updated_at' => Carbon::now()->subDays(8)->addMinutes(8),
                ]);

                // Completed P2P Sell Order
                P2POrder::create([
                    'order_number' => 'P2P' . strtoupper(Str::random(10)),
                    'ad_id' => $p2pAd->id,
                    'buyer_id' => $counterparty->id,
                    'seller_id' => $user->id,
                    'crypto_amount' => 150.00,
                    'escrow_fee' => 0.00,
                    'fiat_amount' => 19725.00,
                    'price' => 131.50,
                    'payment_method' => 'M-Pesa',
                    'payment_details' => ['phone' => '+254712345678'],
                    'status' => 'completed',
                    'paid_at' => Carbon::now()->subDays(3)->addMinutes(5),
                    'completed_at' => Carbon::now()->subDays(3)->addMinutes(7),
                    'created_at' => Carbon::now()->subDays(3),
                    'updated_at' => Carbon::now()->subDays(3)->addMinutes(7),
                ]);
            } catch (\Throwable $e) {
                // Table might not exist or columns differ
            }

            // -------------------------------------------------------------
            // 7. MMF 18% MUTUAL FUND SUBSCRIPTIONS & EARNED INTEREST LOGS
            // -------------------------------------------------------------
            try {
                // Active MMF Subscription ($500 locked at 18%)
                $activeMmf = MmfSubscription::create([
                    'user_id' => $user->id,
                    'amount' => 500.00,
                    'expected_interest' => 7.50, // 18% per annum / 12 months = 1.5% monthly = $7.50
                    'locked_at' => Carbon::now()->subDays(10),
                    'unlocks_at' => Carbon::now()->addDays(20),
                    'status' => 'active',
                    'created_at' => Carbon::now()->subDays(10),
                    'updated_at' => Carbon::now()->subDays(10),
                ]);

                // Completed MMF Subscription ($1,000 previously locked and paid out)
                $completedMmf = MmfSubscription::create([
                    'user_id' => $user->id,
                    'amount' => 1000.00,
                    'expected_interest' => 15.00,
                    'locked_at' => Carbon::now()->subDays(45),
                    'unlocks_at' => Carbon::now()->subDays(15),
                    'status' => 'completed',
                    'created_at' => Carbon::now()->subDays(45),
                    'updated_at' => Carbon::now()->subDays(15),
                ]);

                MmfInterestLog::create([
                    'user_id' => $user->id,
                    'mmf_subscription_id' => $completedMmf->id,
                    'amount' => 15.00,
                    'paid_at' => Carbon::now()->subDays(15),
                    'created_at' => Carbon::now()->subDays(15),
                    'updated_at' => Carbon::now()->subDays(15),
                ]);
            } catch (\Throwable $e) {
                // Ignore if MMF table isn't migrated
            }

            $this->command?->info("Successfully seeded complete transaction portfolio for: {$user->email}!");
        }
    }
}
