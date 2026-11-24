<?php

namespace App\Services;

use App\Models\MmfSubscription;
use App\Models\MmfInterestLog;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MmfService
{
    /**
     * Process all matured MMF subscriptions, returning principal + 18% monthly yield
     * directly into the user's available USDT balance, logging the earned interest,
     * and marking the subscription as completed.
     *
     * @param int|null $userId Optional: process for a specific user ID
     * @return int Number of subscriptions unlocked
     */
    public static function processMaturities(?int $userId = null): int
    {
        $query = MmfSubscription::where('status', 'locked')
            ->where('unlocks_at', '<=', now());

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $maturedSubscriptions = $query->get();
        $unlockedCount = 0;

        foreach ($maturedSubscriptions as $sub) {
            $processed = DB::transaction(function () use ($sub) {
                // Re-query with lock to avoid race conditions
                $lockedSub = MmfSubscription::where('id', $sub->id)
                    ->where('status', 'locked')
                    ->lockForUpdate()
                    ->first();

                if (!$lockedSub) {
                    return false;
                }

                $user = User::find($lockedSub->user_id);
                if (!$user) {
                    return false;
                }

                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => false],
                    ['available_balance' => 0.00, 'locked_balance' => 0.00]
                );

                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

                $principal = (float) $lockedSub->amount;
                $interest = (float) $lockedSub->expected_interest;

                // Fallback: If expected interest was somehow 0, calculate 18% APY yield
                if ($interest <= 0 && $principal > 0) {
                    $interest = round($principal * 0.18, 4);
                }

                $totalPayout = (float) bcadd((string)$principal, (string)$interest, 8);

                // Credit total principal + interest back to wallet available balance
                $wallet->available_balance = (float) bcadd((string)$wallet->available_balance, (string)$totalPayout, 8);
                $wallet->save();

                // Create interest payout log (for dashboard "Total Earned Interest" tally)
                MmfInterestLog::firstOrCreate(
                    [
                        'mmf_subscription_id' => $lockedSub->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'amount' => $interest,
                        'paid_at' => now(),
                    ]
                );

                // Update status to completed
                $lockedSub->update([
                    'status' => 'completed',
                ]);

                Log::info("MMF Subscription #{$lockedSub->id} matured & unlocked for user {$user->id} ({$user->email}). Principal: \${$principal}, Interest: \${$interest}, Total credited: \${$totalPayout}");

                return true;
            });

            if ($processed) {
                $unlockedCount++;
            }
        }

        return $unlockedCount;
    }
}
