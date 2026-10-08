<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\P2PAd;
use App\Models\P2PMessage;
use App\Models\P2POrder;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminP2PController extends Controller
{
    /**
     * Display P2P Admin / Moderator Management Console.
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'is_admin', 'is_moderator', 'is_p2p_merchant', 'p2p_merchant_name', 'p2p_completion_rate', 'p2p_completed_trades', 'created_at')
            ->latest()
            ->get();

        $ads = P2PAd::with('user:id,name,email,p2p_merchant_name')
            ->latest()
            ->get();

        $disputes = P2POrder::with(['buyer:id,name,email', 'seller:id,name,email', 'disputer:id,name,email', 'messages.user:id,name'])
            ->whereIn('status', ['disputed', 'paid', 'pending_payment'])
            ->latest()
            ->get();

        $completedOrdersCount = P2POrder::where('status', 'completed')->count();
        $activeEscrowVolume = P2POrder::whereIn('status', ['pending_payment', 'paid', 'disputed'])->sum('crypto_amount');

        return Inertia::render('Admin/P2P', [
            'users' => $users,
            'ads' => $ads,
            'disputes' => $disputes,
            'stats' => [
                'merchants_count' => $users->where('is_p2p_merchant', true)->count(),
                'active_ads_count' => $ads->where('status', 'active')->count(),
                'disputed_count' => $disputes->where('status', 'disputed')->count(),
                'completed_orders_count' => $completedOrdersCount,
                'active_escrow_volume' => (float) $activeEscrowVolume,
            ],
            'currentUser' => $request->user(),
        ]);
    }

    /**
     * Add or update P2P Merchant privileges.
     */
    public function toggleMerchant(Request $request, User $user)
    {
        $request->validate([
            'is_p2p_merchant' => 'required|boolean',
            'p2p_merchant_name' => 'nullable|string|max:100',
        ]);

        $user->update([
            'is_p2p_merchant' => $request->is_p2p_merchant,
            'p2p_merchant_name' => $request->p2p_merchant_name ?: $user->name,
        ]);

        $action = $request->is_p2p_merchant ? 'granted P2P Merchant status' : 'revoked P2P Merchant status';
        return redirect()->back()->with('message', "Successfully {$action} for {$user->name}.");
    }

    /**
     * Appoint or remove Moderator privileges (Admin only).
     */
    public function toggleModerator(Request $request, User $user)
    {
        if (!$request->user()->is_admin) {
            abort(403, 'Only master administrators can appoint moderators.');
        }

        $request->validate([
            'is_moderator' => 'required|boolean',
        ]);

        $user->update([
            'is_moderator' => $request->is_moderator,
        ]);

        $action = $request->is_moderator ? 'promoted to Moderator' : 'removed from Moderator';
        return redirect()->back()->with('message', "Successfully {$action} for {$user->name}.");
    }

    /**
     * Resolve a disputed P2P order.
     */
    public function resolveDispute(Request $request, P2POrder $order)
    {
        $request->validate([
            'resolution' => 'required|in:release_to_buyer,refund_seller',
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($order->status !== 'disputed' && $order->status !== 'paid') {
            return redirect()->back()->withErrors(['message' => 'This order is not currently in a disputed state.']);
        }

        DB::beginTransaction();
        try {
            // Lock order for update
            $order = P2POrder::where('id', $order->id)->lockForUpdate()->first();

            $sellerWallet = Wallet::where('user_id', $order->seller_id)
                ->where('currency', 'USDT')
                ->where('is_demo', false)
                ->lockForUpdate()
                ->first();

            $buyerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->buyer_id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 0.00, 'locked_balance' => 0.00]
            );

            if ($request->resolution === 'release_to_buyer') {
                // Escrow releases to buyer
                if ($sellerWallet->locked_balance < $order->crypto_amount) {
                    throw new Exception('Seller locked balance is insufficient to complete release.');
                }

                $sellerWallet->decrement('locked_balance', $order->crypto_amount);
                $buyerWallet->increment('available_balance', $order->crypto_amount);

                $order->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Increment seller completed trades
                $order->seller->increment('p2p_completed_trades');

                // System message
                P2PMessage::create([
                    'order_id' => $order->id,
                    'user_id' => null,
                    'is_system' => true,
                    'message' => "⚖️ Dispute resolved by Staff ({$request->user()->name}): Crypto ({$order->crypto_amount} USDT) has been forcibly released to the Buyer. Staff Note: {$request->admin_notes}",
                ]);
            } else {
                // Refund seller
                if ($sellerWallet->locked_balance < $order->crypto_amount) {
                    throw new Exception('Seller locked balance is insufficient to cancel escrow.');
                }

                $sellerWallet->decrement('locked_balance', $order->crypto_amount);
                $sellerWallet->increment('available_balance', $order->crypto_amount);

                // Re-credit the ad's available amount
                if ($order->ad) {
                    $order->ad->increment('available_amount', $order->crypto_amount);
                }

                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => 'admin',
                ]);

                // System message
                P2PMessage::create([
                    'order_id' => $order->id,
                    'user_id' => null,
                    'is_system' => true,
                    'message' => "⚖️ Dispute resolved by Staff ({$request->user()->name}): Escrow cancelled and crypto ({$order->crypto_amount} USDT) refunded to the Seller. Staff Note: {$request->admin_notes}",
                ]);
            }

            DB::commit();
            return redirect()->back()->with('message', "Dispute resolved successfully as {$request->resolution}.");
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Error resolving dispute: ' . $e->getMessage()]);
        }
    }
}
