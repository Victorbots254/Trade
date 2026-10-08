<?php

namespace App\Http\Controllers\P2P;

use App\Http\Controllers\Controller;
use App\Models\P2PAd;
use App\Models\P2PMessage;
use App\Models\P2POrder;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class P2POrderController extends Controller
{
    /**
     * Create a new P2P Escrow Order against an Ad.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:p2p_ads,id',
            'amount_type' => 'required|in:crypto,fiat',
            'amount' => 'required|numeric|gt:0',
            'payment_method' => 'required|string',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $ad = P2PAd::where('id', $request->ad_id)->lockForUpdate()->first();

            if (!$ad || $ad->status !== 'active') {
                return redirect()->back()->withErrors(['message' => 'This ad is no longer active.']);
            }

            if ($ad->user_id === $user->id) {
                return redirect()->back()->withErrors(['message' => 'You cannot trade with your own advertisement.']);
            }

            // Effective unit price: If fiat is USDT or USD, unit price is strictly 1:1 ($1.00)
            $effectivePrice = (in_array($ad->fiat, ['USDT', 'USD']) || (float)$ad->price <= 0) ? 1.00 : (float)$ad->price;

            // Calculate exact crypto and fiat amounts
            if ($request->amount_type === 'crypto') {
                $cryptoAmount = round((float) $request->amount, 4);
                $fiatAmount = in_array($ad->fiat, ['USDT', 'USD']) ? round($cryptoAmount, 2) : round($cryptoAmount * $effectivePrice, 2);
            } else {
                $fiatAmount = round((float) $request->amount, 2);
                $cryptoAmount = in_array($ad->fiat, ['USDT', 'USD']) ? round($fiatAmount, 4) : round($fiatAmount / $effectivePrice, 4);
            }

            // Calculate escrow fee: Free up to $50.00; 0.50 USDT fee for amounts > $50.00
            $escrowFee = ($cryptoAmount > 50.00) ? 0.5000 : 0.0000;

            // Enforce ad limits
            if ($fiatAmount < $ad->min_limit) {
                return redirect()->back()->withErrors([
                    'message' => "Order amount is below the ad's minimum limit of " . number_format($ad->min_limit, 2) . " {$ad->fiat}.",
                ]);
            }

            if ($fiatAmount > $ad->max_limit) {
                return redirect()->back()->withErrors([
                    'message' => "Order amount exceeds the ad's maximum limit of " . number_format($ad->max_limit, 2) . " {$ad->fiat}.",
                ]);
            }

            if ($cryptoAmount > $ad->available_amount) {
                return redirect()->back()->withErrors([
                    'message' => "Requested amount exceeds the available quantity of " . number_format($ad->available_amount, 4) . " {$ad->asset}.",
                ]);
            }

            // Determine buyer & seller
            if ($ad->type === 'sell') {
                // Merchant sells crypto to user
                $sellerId = $ad->user_id;
                $buyerId = $user->id;
            } else {
                // Merchant buys crypto from user
                $sellerId = $user->id;
                $buyerId = $ad->user_id;
            }

            // Escrow lock: deduct seller's available balance and lock it
            $sellerWallet = Wallet::firstOrCreate(
                ['user_id' => $sellerId, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 0.00, 'locked_balance' => 0.00]
            );

            // Re-fetch with lock
            $sellerWallet = Wallet::where('id', $sellerWallet->id)->lockForUpdate()->first();

            if ($sellerWallet->available_balance < $cryptoAmount) {
                return redirect()->back()->withErrors([
                    'message' => 'Seller does not have enough available balance to cover the escrow for this trade.',
                ]);
            }

            // Atomically lock escrow
            $sellerWallet->decrement('available_balance', $cryptoAmount);
            $sellerWallet->increment('locked_balance', $cryptoAmount);

            // Deduct ad available inventory
            $ad->decrement('available_amount', $cryptoAmount);
            if ($ad->available_amount <= 0) {
                $ad->update(['status' => 'paused']);
            }

            $orderNumber = 'P2P-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $timeLimit = $ad->time_limit_minutes ?: 15;

            $order = P2POrder::create([
                'order_number' => $orderNumber,
                'ad_id' => $ad->id,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'crypto_amount' => $cryptoAmount,
                'escrow_fee' => $escrowFee,
                'fiat_amount' => $fiatAmount,
                'price' => $effectivePrice,
                'payment_method' => $request->payment_method,
                'payment_details' => $ad->payment_details ?: [],
                'status' => 'pending_payment',
                'expires_at' => now()->addMinutes($timeLimit),
            ]);

            // Auto-reply message if configured
            if (!empty($ad->auto_reply)) {
                P2PMessage::create([
                    'order_id' => $order->id,
                    'user_id' => $sellerId,
                    'message' => $ad->auto_reply,
                    'is_system' => false,
                ]);
            }

            // Initial system notice
            $feeNotice = $escrowFee > 0 ? " (Platform Escrow Fee: {$escrowFee} USDT upon completion)" : " (Platform Escrow Fee: Free $0.00)";
            $netReceive = max(0, round($cryptoAmount - $escrowFee, 4));
            P2PMessage::create([
                'order_id' => $order->id,
                'user_id' => null,
                'is_system' => true,
                'message' => "🛡️ Trade started. {$cryptoAmount} USDT is safely locked in TradeCo Escrow. Buyer receives {$netReceive} USDT{$feeNotice}. Buyer has {$timeLimit} minutes to send payment.",
            ]);

            // Dispatch email notification to seller
            \App\Services\P2PNotificationService::notifyOrderCreated($order);

            return redirect()->route('p2p.order.show', ['order' => $order->id]);
        });
    }

    /**
     * Display live P2P Order Escrow Room.
     */
    public function show(P2POrder $order, Request $request)
    {
        $user = $request->user();

        // Check authorization (buyer, seller, or admin)
        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized access to this P2P order.');
        }

        // Auto-expire order if past expiration and still in pending_payment
        if ($order->status === 'pending_payment' && now()->gt($order->expires_at)) {
            $this->executeOrderTimeout($order);
            $order->refresh();
        }

        $order->load([
            'ad:id,type,asset,fiat,terms,auto_reply',
            'buyer:id,name,email,p2p_merchant_name',
            'seller:id,name,email,p2p_merchant_name,p2p_payment_details',
            'messages.user:id,name',
        ]);

        return Inertia::render('P2P/Order', [
            'order' => $order,
            'currentUser' => $user,
            'isBuyer' => $order->buyer_id === $user->id,
            'isSeller' => $order->seller_id === $user->id,
            'isAdminOrMod' => (bool) $user->is_admin,
            'isAdmin' => (bool) $user->is_admin,
        ]);
    }

    /**
     * Buyer marks payment as sent.
     */
    public function markPaid(Request $request, P2POrder $order)
    {
        $user = $request->user();

        if ($order->buyer_id !== $user->id) {
            abort(403, 'Only the buyer can mark the order as paid.');
        }

        if ($order->status !== 'pending_payment') {
            return redirect()->back()->withErrors(['message' => 'Order is not in pending payment status.']);
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        P2PMessage::create([
            'order_id' => $order->id,
            'user_id' => null,
            'is_system' => true,
            'message' => "💸 Buyer marked payment as sent! Seller, please confirm receipt of {$order->fiat_amount} {$order->ad->fiat} in your account before releasing.",
        ]);

        // Dispatch email notification to seller
        \App\Services\P2PNotificationService::notifyPaymentSent($order);

        return redirect()->back()->with('message', 'Payment marked as sent! Waiting for seller to release crypto.');
    }

    /**
     * Seller releases crypto to buyer.
     */
    public function release(Request $request, P2POrder $order)
    {
        $user = $request->user();

        if ($order->seller_id !== $user->id && !$user->is_admin) {
            abort(403, 'Only the seller can release crypto.');
        }

        if (!in_array($order->status, ['pending_payment', 'paid', 'disputed'])) {
            return redirect()->back()->withErrors(['message' => "Order cannot be released because it is currently in '{$order->status}' status."]);
        }

        DB::beginTransaction();
        try {
            $order = P2POrder::where('id', $order->id)->lockForUpdate()->first();

            $sellerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->seller_id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 0.00, 'locked_balance' => 0.00]
            );
            $sellerWallet = Wallet::where('id', $sellerWallet->id)->lockForUpdate()->first();

            $buyerWallet = Wallet::firstOrCreate(
                ['user_id' => $order->buyer_id, 'currency' => 'USDT', 'is_demo' => false],
                ['available_balance' => 0.00, 'locked_balance' => 0.00]
            );
            $buyerWallet = Wallet::where('id', $buyerWallet->id)->lockForUpdate()->first();

            // Safely deduct escrow funds from seller (locked balance first, then available)
            $deductLocked = min((float) $sellerWallet->locked_balance, (float) $order->crypto_amount);
            if ($deductLocked > 0) {
                $sellerWallet->decrement('locked_balance', $deductLocked);
            }
            $remainingDeduct = (float) $order->crypto_amount - $deductLocked;
            if ($remainingDeduct > 0 && (float) $sellerWallet->available_balance >= $remainingDeduct) {
                $sellerWallet->decrement('available_balance', $remainingDeduct);
            }

            // Calculate net crypto for buyer deducting platform escrow fee
            $escrowFee = (float) ($order->escrow_fee ?? 0);
            $netCrypto = max(0, round($order->crypto_amount - $escrowFee, 4));

            // Transfer crypto to buyer
            $buyerWallet->increment('available_balance', $netCrypto);

            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Increment seller completed trades
            $order->seller->increment('p2p_completed_trades');

            $feeMsg = $escrowFee > 0 ? " ({$escrowFee} USDT escrow fee deducted)" : " (Zero escrow fee)";
            P2PMessage::create([
                'order_id' => $order->id,
                'user_id' => null,
                'is_system' => true,
                'message' => "🎉 Crypto successfully released! {$netCrypto} USDT deposited into Buyer's wallet{$feeMsg}.",
            ]);

            DB::commit();

            // Dispatch email notification to buyer
            \App\Services\P2PNotificationService::notifyCryptoReleased($order);

            return redirect()->back()->with('message', 'Crypto released successfully! Trade complete.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Error releasing crypto: ' . $e->getMessage()]);
        }
    }

    /**
     * Buyer cancels order before payment.
     */
    public function cancel(Request $request, P2POrder $order)
    {
        $user = $request->user();

        if ($order->buyer_id !== $user->id && !$user->is_admin) {
            abort(403, 'Only the buyer or admin can cancel the order.');
        }

        if ($order->status !== 'pending_payment') {
            return redirect()->back()->withErrors(['message' => 'Cannot cancel an order that has already been marked as paid.']);
        }

        DB::beginTransaction();
        try {
            $this->executeOrderCancellation($order, 'buyer');
            DB::commit();
            return redirect()->back()->with('message', 'Order cancelled successfully. Escrow refunded to seller.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Error cancelling order: ' . $e->getMessage()]);
        }
    }

    /**
     * Open a dispute / appeal on an order.
     */
    public function dispute(Request $request, P2POrder $order)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = $request->user();

        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
            abort(403);
        }

        if ($order->status !== 'paid' && $order->status !== 'pending_payment') {
            return redirect()->back()->withErrors(['message' => 'Cannot dispute completed or cancelled orders.']);
        }

        $order->update([
            'status' => 'disputed',
            'disputed_at' => now(),
            'disputed_by' => $user->id,
            'dispute_reason' => $request->reason,
        ]);

        P2PMessage::create([
            'order_id' => $order->id,
            'user_id' => null,
            'is_system' => true,
            'message' => "⚠️ Dispute opened by {$user->name}. Reason: {$request->reason}. An Admin will review evidence in this chat and resolve.",
        ]);

        return redirect()->back()->with('message', 'Dispute reported. Trade locked for admin review.');
    }

    /**
     * Send in-order message or payment screenshot.
     */
    public function sendMessage(Request $request, P2POrder $order)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();

        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id && !$user->is_admin) {
            abort(403);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('p2p_receipts', 'public');
        }

        if (!$request->message && !$attachmentPath) {
            return redirect()->back();
        }

        $msg = P2PMessage::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'is_system' => false,
        ]);

        // Dispatch email notification to other party
        \App\Services\P2PNotificationService::notifyNewMessage($order, $msg, $user);

        return redirect()->back();
    }

    /**
     * Internal helper to cancel and refund escrow.
     */
    private function executeOrderCancellation(P2POrder $order, string $cancelledBy)
    {
        $sellerWallet = Wallet::where('user_id', $order->seller_id)
            ->where('currency', 'USDT')
            ->where('is_demo', false)
            ->lockForUpdate()
            ->first();

        if ($sellerWallet && $sellerWallet->locked_balance >= $order->crypto_amount) {
            $sellerWallet->decrement('locked_balance', $order->crypto_amount);
            $sellerWallet->increment('available_balance', $order->crypto_amount);
        }

        // Return amount to ad inventory
        if ($order->ad) {
            $order->ad->increment('available_amount', $order->crypto_amount);
            if ($order->ad->status === 'paused') {
                $order->ad->update(['status' => 'active']);
            }
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
        ]);

        P2PMessage::create([
            'order_id' => $order->id,
            'user_id' => null,
            'is_system' => true,
            'message' => "❌ Order cancelled by {$cancelledBy}. Escrow ({$order->crypto_amount} USDT) returned to Seller.",
        ]);
    }

    /**
     * Internal helper to handle payment window timeout.
     */
    private function executeOrderTimeout(P2POrder $order)
    {
        DB::transaction(function () use ($order) {
            $this->executeOrderCancellation($order, 'system_timeout');
        });
    }

    /**
     * Poll active P2P trade notifications and new chat messages for the logged-in user.
     */
    public function pollNotifications(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['orders' => [], 'latest_message' => null]);
        }

        $activeOrders = P2POrder::with(['buyer:id,name', 'seller:id,name', 'ad'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->whereIn('status', ['pending_payment', 'paid', 'disputed'])
            ->latest()
            ->get();

        $orderIds = $activeOrders->pluck('id')->toArray();

        $latestMessage = null;
        if (!empty($orderIds)) {
            $msg = P2PMessage::with('user:id,name')
                ->whereIn('order_id', $orderIds)
                ->where('is_system', false)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', '!=', $user->id)
                        ->orWhereNull('user_id');
                })
                ->latest()
                ->first();

            if ($msg) {
                $order = $activeOrders->firstWhere('id', $msg->order_id);
                $latestMessage = [
                    'id' => $msg->id,
                    'order_id' => $msg->order_id,
                    'order_number' => $order ? $order->order_number : '',
                    'sender_name' => $msg->user ? $msg->user->name : 'Trader',
                    'message' => $msg->message ?: '[Image attachment]',
                    'created_at' => $msg->created_at->toISOString(),
                ];
            }
        }

        $formattedOrders = $activeOrders->map(function ($o) use ($user) {
            $isSeller = $o->seller_id === $user->id;
            return [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'is_seller' => $isSeller,
                'counterparty_name' => $isSeller ? ($o->buyer->name ?? 'Buyer') : ($o->seller->name ?? 'Seller'),
                'crypto_amount' => (float) $o->crypto_amount,
                'fiat_amount' => (float) $o->fiat_amount,
                'fiat' => $o->ad ? $o->ad->fiat : 'USDT',
                'created_at' => $o->created_at->toISOString(),
                'updated_at' => $o->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'orders' => $formattedOrders,
            'latest_message' => $latestMessage,
        ]);
    }
}
