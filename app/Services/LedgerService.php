<?php

namespace App\Services;

use App\Models\Account;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LedgerService
{
    /**
     * Get or create a system double-entry account by code.
     */
    public static function getOrCreateAccount(string $code, string $name, string $type, string $currency): Account
    {
        return Account::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'type' => $type, 'currency' => $currency]
        );
    }

    /**
     * Record deposit approval credit to user's available balance.
     */
    public static function recordDepositCredit(Wallet $wallet, float|string $amount, int $depositId): void
    {
        $amount = (string) $amount;

        DB::transaction(function () use ($wallet, $amount, $depositId) {
            // Lock wallet row
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $wallet->available_balance = bcadd((string)$wallet->available_balance, (string)$amount, 8);
            $wallet->save();

            $systemAccount = self::getOrCreateAccount(
                'SYSTEM_DEPOSIT_REVENUE_' . $wallet->currency,
                'System Deposit Reserve (' . $wallet->currency . ')',
                'asset',
                $wallet->currency
            );

            $userAccount = self::getOrCreateAccount(
                'USER_AVAIL_' . $wallet->user_id . '_' . $wallet->currency,
                'User Available Balance (' . $wallet->currency . ')',
                'liability',
                $wallet->currency
            );

            $txId = (string) Str::uuid();

            // Debit System Reserve Asset
            LedgerEntry::create([
                'transaction_id' => $txId,
                'account_id' => $systemAccount->id,
                'wallet_id' => null,
                'type' => 'debit',
                'amount' => $amount,
                'reference_type' => 'Deposit',
                'reference_id' => $depositId,
                'description' => "System credited deposit #{$depositId}",
            ]);

            // Credit User Liability
            LedgerEntry::create([
                'transaction_id' => $txId,
                'account_id' => $userAccount->id,
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'reference_type' => 'Deposit',
                'reference_id' => $depositId,
                'description' => "Deposit credit to wallet #{$wallet->id}",
            ]);
        });
    }

    /**
     * Lock funds from available balance when placing an order.
     */
    public static function lockFundsForOrder(Wallet $wallet, float|string $amount, int $orderId): void
    {
        $amount = (float) $amount;

        DB::transaction(function () use ($wallet, $amount, $orderId) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if (bcsub((string)$wallet->available_balance, (string)$amount, 8) < 0) {
                throw new InvalidArgumentException("Insufficient available balance in {$wallet->currency} wallet.");
            }

            $wallet->available_balance = bcsub((string)$wallet->available_balance, (string)$amount, 8);
            $wallet->locked_balance = bcadd((string)$wallet->locked_balance, (string)$amount, 8);
            $wallet->save();

            $availAcc = self::getOrCreateAccount(
                'USER_AVAIL_' . $wallet->user_id . '_' . $wallet->currency,
                'User Available Balance (' . $wallet->currency . ')',
                'liability',
                $wallet->currency
            );

            $lockedAcc = self::getOrCreateAccount(
                'USER_LOCKED_' . $wallet->user_id . '_' . $wallet->currency,
                'User Locked Balance (' . $wallet->currency . ')',
                'liability',
                $wallet->currency
            );

            $txId = (string) Str::uuid();

            LedgerEntry::create([
                'transaction_id' => $txId,
                'account_id' => $availAcc->id,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'reference_type' => 'Order',
                'reference_id' => $orderId,
                'description' => "Lock funds for order #{$orderId}",
            ]);

            LedgerEntry::create([
                'transaction_id' => $txId,
                'account_id' => $lockedAcc->id,
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'reference_type' => 'Order',
                'reference_id' => $orderId,
                'description' => "Transfer to locked balance for order #{$orderId}",
            ]);
        });
    }

    /**
     * Unlock remaining funds for cancelled or partially filled order.
     */
    public static function unlockFundsForOrder(Wallet $wallet, float|string $amount, int $orderId): void
    {
        $amount = (float) $amount;

        DB::transaction(function () use ($wallet, $amount, $orderId) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $unlockAmt = min((float)$wallet->locked_balance, $amount);
            if ($unlockAmt <= 0) return;

            $wallet->locked_balance = bcsub((string)$wallet->locked_balance, (string)$unlockAmt, 8);
            $wallet->available_balance = bcadd((string)$wallet->available_balance, (string)$unlockAmt, 8);
            $wallet->save();

            $availAcc = self::getOrCreateAccount(
                'USER_AVAIL_' . $wallet->user_id . '_' . $wallet->currency,
                'User Available Balance (' . $wallet->currency . ')',
                'liability',
                $wallet->currency
            );

            $lockedAcc = self::getOrCreateAccount(
                'USER_LOCKED_' . $wallet->user_id . '_' . $wallet->currency,
                'User Locked Balance (' . $wallet->currency . ')',
                'liability',
                $wallet->currency
            );

            $txId = (string) Str::uuid();

            LedgerEntry::create([
                'transaction_id' => $txId,
                'account_id' => $lockedAcc->id,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $unlockAmt,
                'reference_type' => 'OrderCancel',
                'reference_id' => $orderId,
                'description' => "Unlock remaining funds for order #{$orderId}",
            ]);

            LedgerEntry::create([
                'transaction_id' => $txId,
                'account_id' => $availAcc->id,
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $unlockAmt,
                'reference_type' => 'OrderCancel',
                'reference_id' => $orderId,
                'description' => "Return locked funds to available balance for order #{$orderId}",
            ]);
        });
    }

    /**
     * Settle executed trade between buyer and seller atomically.
     */
    public static function settleTrade(Order $buyOrder, Order $sellOrder, float|string $price, float|string $qty, int $tradeId): void
    {
        DB::transaction(function () use ($buyOrder, $sellOrder, $price, $qty, $tradeId) {
            $market = $buyOrder->market;
            $quoteCurrency = $market->quote_currency; // e.g. USDT
            $baseCurrency = $market->base_currency;   // e.g. BTC

            $tradeCost = bcmul((string)$price, (string)$qty, 8);

            // 1. Buyer: Locked USDT decreased, Base Currency Available increased
            $buyerQuoteWallet = Wallet::where('user_id', $buyOrder->user_id)->where('currency', $quoteCurrency)->lockForUpdate()->first();
            $buyerBaseWallet  = Wallet::firstOrCreate(
                ['user_id' => $buyOrder->user_id, 'currency' => $baseCurrency],
                ['available_balance' => 0, 'locked_balance' => 0]
            );
            $buyerBaseWallet = Wallet::where('id', $buyerBaseWallet->id)->lockForUpdate()->first();

            // Deduct quote cost from buyer locked balance
            $buyerQuoteWallet->locked_balance = max(0, bcsub((string)$buyerQuoteWallet->locked_balance, (string)$tradeCost, 8));
            $buyerQuoteWallet->save();

            // Add base qty to buyer available balance
            $buyerBaseWallet->available_balance = bcadd((string)$buyerBaseWallet->available_balance, (string)$qty, 8);
            $buyerBaseWallet->save();

            // 2. Seller: Locked Base Currency decreased, Quote Currency Available increased
            $sellerBaseWallet = Wallet::where('user_id', $sellOrder->user_id)->where('currency', $baseCurrency)->lockForUpdate()->first();
            $sellerQuoteWallet = Wallet::firstOrCreate(
                ['user_id' => $sellOrder->user_id, 'currency' => $quoteCurrency],
                ['available_balance' => 0, 'locked_balance' => 0]
            );
            $sellerQuoteWallet = Wallet::where('id', $sellerQuoteWallet->id)->lockForUpdate()->first();

            // Deduct base qty from seller locked balance
            $sellerBaseWallet->locked_balance = max(0, bcsub((string)$sellerBaseWallet->locked_balance, (string)$qty, 8));
            $sellerBaseWallet->save();

            // Add quote cost to seller available balance
            $sellerQuoteWallet->available_balance = bcadd((string)$sellerQuoteWallet->available_balance, (string)$tradeCost, 8);
            $sellerQuoteWallet->save();

            // Ledger entries for trade settlement
            $txId = (string) Str::uuid();

            $buyerQuoteAcc = self::getOrCreateAccount('USER_LOCKED_' . $buyOrder->user_id . '_' . $quoteCurrency, 'User Locked Quote', 'liability', $quoteCurrency);
            $buyerBaseAcc  = self::getOrCreateAccount('USER_AVAIL_' . $buyOrder->user_id . '_' . $baseCurrency, 'User Avail Base', 'liability', $baseCurrency);
            $sellerBaseAcc = self::getOrCreateAccount('USER_LOCKED_' . $sellOrder->user_id . '_' . $baseCurrency, 'User Locked Base', 'liability', $baseCurrency);
            $sellerQuoteAcc = self::getOrCreateAccount('USER_AVAIL_' . $sellOrder->user_id . '_' . $quoteCurrency, 'User Avail Quote', 'liability', $quoteCurrency);

            LedgerEntry::create(['transaction_id' => $txId, 'account_id' => $buyerQuoteAcc->id, 'wallet_id' => $buyerQuoteWallet->id, 'type' => 'debit', 'amount' => $tradeCost, 'reference_type' => 'Trade', 'reference_id' => $tradeId, 'description' => "Settle trade #{$tradeId} buyer quote payout"]);
            LedgerEntry::create(['transaction_id' => $txId, 'account_id' => $buyerBaseAcc->id, 'wallet_id' => $buyerBaseWallet->id, 'type' => 'credit', 'amount' => $qty, 'reference_type' => 'Trade', 'reference_id' => $tradeId, 'description' => "Settle trade #{$tradeId} buyer base receipt"]);
            LedgerEntry::create(['transaction_id' => $txId, 'account_id' => $sellerBaseAcc->id, 'wallet_id' => $sellerBaseWallet->id, 'type' => 'debit', 'amount' => $qty, 'reference_type' => 'Trade', 'reference_id' => $tradeId, 'description' => "Settle trade #{$tradeId} seller base payout"]);
            LedgerEntry::create(['transaction_id' => $txId, 'account_id' => $sellerQuoteAcc->id, 'wallet_id' => $sellerQuoteWallet->id, 'type' => 'credit', 'amount' => $tradeCost, 'reference_type' => 'Trade', 'reference_id' => $tradeId, 'description' => "Settle trade #{$tradeId} seller quote receipt"]);
        });
    }
}
