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
            'merchantName' => $user->p2p_merchant_name ?: $user->name,
            'completionRate' => (float) $user->p2p_completion_rate,
            'completedTrades' => (int) $user->p2p_completed_trades,
            'usdtBalance' => (float) $wallet->available_balance,
            'marketRateKes' => $marketRateKes,
            'ads' => $ads,
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

        $ad = P2PAd::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'asset' => $request->asset,
            'fiat' => $request->fiat,
            'price' => $request->price,
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
}
