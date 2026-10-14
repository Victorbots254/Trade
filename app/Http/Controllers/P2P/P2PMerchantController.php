<?php

namespace App\Http\Controllers\P2P;

use App\Http\Controllers\Controller;
use App\Models\P2PAd;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class P2PMerchantController extends Controller
{
    /**
     * Merchant Ads Management Portal.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $ads = P2PAd::where('user_id', $user->id)
            ->latest()
            ->get();

        // Automatically reconcile user's live USDT wallet to release any stranded locked funds
        $wallet = \App\Services\WalletReconciliationService::reconcileUsdtWallet($user, false);

        $marketRateKes = (float) \App\Services\MegaPayService::getExchangeRate();
        if ($marketRateKes <= 0) $marketRateKes = 129.50;

        return Inertia::render('P2P/MerchantAds', [
            'isMerchant' => (bool) ($user->is_p2p_merchant || $user->is_admin),
            'merchantName' => $user->p2p_merchant_name ?: ($user->username ?: $user->name),
            'username' => $user->username ?: $user->name,
            'paymentDetails' => is_array($user->p2p_payment_details) ? $user->p2p_payment_details : [],
            'completionRate' => (float) $user->p2p_completion_rate,
            'completedTrades' => (int) $user->p2p_completed_trades,
            'usdtBalance' => (float) $wallet->available_balance,
            'marketRateKes' => $marketRateKes,
            'ads' => $ads,
            'user' => $user,
        ]);
    }

    /**
     * Publish a new P2P Ad (Gated to verified merchants only).
     */
    public function storeAd(Request $request)
    {
        $user = $request->user();

        if (!$user->is_p2p_merchant && !$user->is_admin) {
            return redirect()->back()->withErrors([
                'message' => 'Only verified P2P Merchants approved by an Admin can post ads.',
            ]);
        }

        // Reconcile user's wallet before validation
        $wallet = \App\Services\WalletReconciliationService::reconcileUsdtWallet($user, false);

        $request->validate([
            'type' => 'required|in:sell,buy',
            'asset' => 'required|string|in:USDT',
            'fiat' => 'required|string|in:KES,USD,USDT',
            'price' => 'required|numeric|min:0.01',
            'total_amount' => 'required|numeric|min:5',
            'min_limit' => 'required|numeric|min:1',
            'max_limit' => 'required|numeric|gt:min_limit',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'in:mpesa,bank_transfer',
            'payment_details' => 'nullable|array',
            'terms' => 'nullable|string|max:1000',
            'auto_reply' => 'nullable|string|max:500',
            'time_limit_minutes' => 'nullable|integer|min:10|max:60',
        ]);

        // If Merchant is selling USDT, verify balance (admins automatically provide liquidity if needed)
        if ($request->type === 'sell') {
            if ($user->is_admin && (float)$wallet->available_balance < (float)$request->total_amount) {
                $wallet->available_balance = max((float)$wallet->available_balance, (float)$request->total_amount);
                $wallet->save();
            }

            if ((float)$wallet->available_balance < (float)$request->total_amount) {
                return redirect()->back()->withErrors([
                    'total_amount' => 'Insufficient live USDT balance to post this sell ad. You have $' . number_format((float)$wallet->available_balance, 2) . ' USDT available. Please deposit funds or adjust your ad quantity.',
                ]);
            }
        }

        // Force 1:1 parity rate when settling in USDT or USD
        $unitPrice = in_array($request->fiat, ['USDT', 'USD']) ? 1.00 : (float) $request->price;

        $ad = P2PAd::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'asset' => $request->asset,
            'fiat' => $request->fiat,
            'price' => $unitPrice,
            'total_amount' => $request->total_amount,
            'available_amount' => $request->total_amount,
            'min_limit' => $request->min_limit,
            'max_limit' => $request->max_limit,
            'payment_methods' => $request->payment_methods,
            'payment_details' => $request->payment_details ?: [],
            'terms' => $request->terms,
            'auto_reply' => $request->auto_reply,
            'time_limit_minutes' => $request->time_limit_minutes ?: 15,
            'status' => 'active',
        ]);

        return redirect()->back()->with('message', 'P2P Ad published successfully!');
    }

    /**
     * Pause or resume an existing ad.
     */
    public function toggleAd(Request $request, P2PAd $ad)
    {
        $user = $request->user();

        if ($ad->user_id !== $user->id && !$user->is_admin) {
            abort(403);
        }

        $newStatus = $ad->status === 'active' ? 'paused' : 'active';
        $ad->update(['status' => $newStatus]);

        return redirect()->back()->with('message', "Ad is now {$newStatus}.");
    }

    /**
     * Close an ad.
     */
    public function closeAd(Request $request, P2PAd $ad)
    {
        $user = $request->user();

        if ($ad->user_id !== $user->id && !$user->is_admin) {
            abort(403);
        }

        $ad->update(['status' => 'closed', 'available_amount' => 0]);

        return redirect()->back()->with('message', 'Ad closed successfully.');
    }

    /**
     * Update or delete P2P payment methods (M-Pesa, Bank Transfer) and trading handle.
     */
    public function updatePaymentDetails(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'username' => 'nullable|string|max:50',
            'p2p_merchant_name' => 'nullable|string|max:100',
            'action' => 'nullable|string|in:save,delete_mpesa,delete_bank',
            'mpesa' => 'nullable|array',
            'bank' => 'nullable|array',
        ]);

        if ($request->filled('username')) {
            $cleanUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $request->username);
            if ($cleanUsername !== $user->username) {
                if (\App\Models\User::where('username', $cleanUsername)->where('id', '!=', $user->id)->exists()) {
                    return redirect()->back()->withErrors(['username' => 'This username is already taken.']);
                }
                $user->username = $cleanUsername;
            }
        }

        if ($request->filled('p2p_merchant_name')) {
            $user->p2p_merchant_name = $request->p2p_merchant_name;
        }

        $existingDetails = is_array($user->p2p_payment_details) ? $user->p2p_payment_details : [];

        if ($request->action === 'delete_mpesa') {
            unset($existingDetails['mpesa']);
            $user->p2p_payment_details = $existingDetails;
            $user->save();
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'M-Pesa payment method removed successfully.',
                    'user' => $user->fresh(),
                    'p2p_payment_details' => $existingDetails,
                ]);
            }
            return redirect()->back()->with('message', 'M-Pesa payment method removed successfully.');
        }

        if ($request->action === 'delete_bank') {
            unset($existingDetails['bank']);
            $user->p2p_payment_details = $existingDetails;
            $user->save();
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Bank payment method removed successfully.',
                    'user' => $user->fresh(),
                    'p2p_payment_details' => $existingDetails,
                ]);
            }
            return redirect()->back()->with('message', 'Bank payment method removed successfully.');
        }

        // Saving / Updating payment details
        if ($request->has('mpesa')) {
            if (empty($request->mpesa['phone']) && empty($request->mpesa['paybill_number'])) {
                unset($existingDetails['mpesa']);
            } else {
                $existingDetails['mpesa'] = [
                    'type' => $request->mpesa['type'] ?? 'phone',
                    'phone' => $request->mpesa['phone'] ?? null,
                    'account_name' => $request->mpesa['account_name'] ?? $user->name,
                    'paybill_number' => $request->mpesa['paybill_number'] ?? null,
                    'account_number' => $request->mpesa['account_number'] ?? null,
                ];
            }
        }

        if ($request->has('bank')) {
            if (empty($request->bank['account_number'])) {
                unset($existingDetails['bank']);
            } else {
                $existingDetails['bank'] = [
                    'bank_name' => $request->bank['bank_name'] ?? 'Equity Bank',
                    'account_number' => $request->bank['account_number'] ?? null,
                    'account_name' => $request->bank['account_name'] ?? $user->name,
                ];
            }
        }

        $user->p2p_payment_details = $existingDetails;
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'P2P payment details updated successfully!',
                'user' => $user->fresh(),
            ]);
        }

        return redirect()->back()->with('message', 'P2P payment details updated successfully!');
    }
}
