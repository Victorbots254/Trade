<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\Wallet;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $marketRateKes = (float) \App\Services\MegaPayService::getExchangeRate();
        if ($marketRateKes <= 0) $marketRateKes = 129.50;

        return Inertia::render("Wallet/Withdraw", [
            "user" => $user,
            "withdrawals" => Withdrawal::where("user_id", $user->id)->latest()->get(),
            "wallets" => Wallet::where("user_id", $user->id)->get(),
            "marketRateKes" => $marketRateKes,
            "savedPaymentDetails" => is_array($user->p2p_payment_details) ? $user->p2p_payment_details : [],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "amount" => "required|numeric|min:10",
            "payment_method" => "required|in:bep20,mpesa",
            "bep20_address" => "nullable|string",
            "mpesa_phone" => "nullable|string|max:30",
            "mpesa_name" => "nullable|string|max:150",
        ]);

        $user = $request->user();
        $paymentMethod = $request->payment_method;
        $amount = (float) $request->amount;

        $marketRateKes = (float) \App\Services\MegaPayService::getExchangeRate();
        if ($marketRateKes <= 0) $marketRateKes = 129.50;

        $bep20Address = null;
        $mpesaPhone = null;
        $mpesaName = null;
        $amountKes = null;

        if ($paymentMethod === 'bep20') {
            $bep20Address = $request->bep20_address ?: $user->bep20_address;
            if (empty($bep20Address)) {
                return back()->withErrors(["bep20_address" => "You must provide or link a Binance BEP-20 wallet address."]);
            }
        } else {
            // M-Pesa (Phone Number Payout)
            $savedMpesa = is_array($user->p2p_payment_details) && isset($user->p2p_payment_details['mpesa']) 
                ? $user->p2p_payment_details['mpesa'] 
                : [];

            $mpesaPhone = $request->mpesa_phone ?: ($savedMpesa['phone'] ?? null);
            $mpesaName = $request->mpesa_name ?: ($savedMpesa['account_name'] ?? $user->name);

            if (empty($mpesaPhone)) {
                return back()->withErrors(["mpesa_phone" => "Please enter your Safaricom M-Pesa phone number."]);
            }

            $amountKes = round($amount * $marketRateKes, 2);
        }

        return DB::transaction(function () use (
            $user, 
            $amount, 
            $paymentMethod, 
            $bep20Address, 
            $mpesaPhone, 
            $mpesaName, 
            $amountKes
        ) {
            $wallet = Wallet::firstOrCreate(
                ["user_id" => $user->id, "currency" => "USDT", "is_demo" => false],
                ["available_balance" => 0, "locked_balance" => 0]
            );

            $wallet = Wallet::where("id", $wallet->id)->lockForUpdate()->first();

            if ((float) $wallet->available_balance < $amount) {
                return back()->withErrors(["amount" => "Insufficient available USDT balance."]);
            }

            // Deduct funds immediately upon withdrawal request
            $wallet->available_balance = (float) bcsub((string)$wallet->available_balance, (string)$amount, 8);
            $wallet->save();

            $withdrawal = Withdrawal::create([
                "user_id" => $user->id,
                "currency" => "USDT",
                "amount" => $amount,
                "payment_method" => $paymentMethod,
                "bep20_address" => $bep20Address,
                "mpesa_phone" => $mpesaPhone,
                "mpesa_name" => $mpesaName,
                "mpesa_type" => 'phone',
                "mpesa_account_number" => null,
                "amount_kes" => $amountKes,
                "status" => "pending",
            ]);

            // Destination summary string for feedback & notification
            $destSummary = $paymentMethod === 'mpesa' 
                ? "M-Pesa ({$mpesaPhone})" 
                : "BEP-20 Wallet ({$bep20Address})";

            // Dispatch Trading Withdrawal Email
            try {
                \App\Services\TradingEmailService::sendWithdrawalNotification(
                    $user,
                    $amount,
                    'USDT',
                    $destSummary,
                    'Pending Processing'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Withdrawal email dispatch error: ' . $e->getMessage());
            }

            $successMsg = $paymentMethod === 'mpesa'
                ? "Your withdrawal request of $" . number_format($amount, 2) . " USDT (KES " . number_format($amountKes, 2) . ") has been submitted successfully! Your payments will be sent to your preferred method (M-Pesa: {$mpesaPhone}) within 24 hours."
                : "Your withdrawal request of $" . number_format($amount, 2) . " USDT has been submitted successfully! Your payments will be sent to your preferred method (BEP-20: {$bep20Address}) within 24 hours.";

            return back()->with("message", $successMsg);
        });
    }
}
