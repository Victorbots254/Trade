<?php

namespace App\Services;

use App\Models\BinaryOptionContract;
use App\Models\Order;
use App\Models\P2POrder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletReconciliationService
{
    /**
     * Reconcile a user's USDT wallet locked vs available balance,
     * releasing any stranded locked funds back into available balance.
     */
    public static function reconcileUsdtWallet(User $user, bool $isDemo = false): Wallet
    {
        return DB::transaction(function () use ($user, $isDemo) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => $isDemo],
                ['available_balance' => ($isDemo ? ($user->demo_balance ?? 10000.00) : 0.00), 'locked_balance' => 0.00]
            );

            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            // 1. Calculate actual active spot buy order commitment
            $spotOrders = Order::where('user_id', $user->id)
                ->whereIn('status', ['open', 'partially_filled'])
                ->where('is_demo', $isDemo)
                ->where('side', 'buy')
                ->get();

            $spotCommitment = 0.0;
            foreach ($spotOrders as $o) {
                $remQty = max(0, (float) bcsub((string)$o->quantity, (string)$o->filled_quantity, 8));
                $spotCommitment += (float) bcmul((string)$o->price, (string)$remQty, 8);
            }

            // 2. Calculate actual active binary options commitment
            $optionsCommitment = 0.0;
            if (class_exists(BinaryOptionContract::class)) {
                $optionsCommitment = (float) BinaryOptionContract::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('is_demo', $isDemo)
                    ->sum('investment_amount');
            }

            // 3. Calculate actual P2P escrow commitment (live only)
            $p2pCommitment = 0.0;
            if (!$isDemo && class_exists(P2POrder::class)) {
                $p2pCommitment = (float) P2POrder::where('seller_id', $user->id)
                    ->whereIn('status', ['pending', 'paid', 'disputed'])
                    ->sum('crypto_amount');
            }

            $totalCommitted = $spotCommitment + $optionsCommitment + $p2pCommitment;

            // If current locked_balance exceeds total legitimate active commitments, release excess
            $currentLocked = (float) $wallet->locked_balance;
            if ($currentLocked > $totalCommitted) {
                $stranded = (float) bcsub((string)$currentLocked, (string)$totalCommitted, 8);
                $wallet->locked_balance = $totalCommitted;
                $wallet->available_balance = (float) bcadd((string)$wallet->available_balance, (string)$stranded, 8);
                $wallet->save();
            } elseif ($currentLocked < 0) {
                $wallet->locked_balance = 0.0;
                $wallet->save();
            }

            // Sync user demo balance
            if ($isDemo) {
                $user->update(['demo_balance' => (float) $wallet->available_balance]);
            }

            return $wallet->fresh();
        });
    }
}
