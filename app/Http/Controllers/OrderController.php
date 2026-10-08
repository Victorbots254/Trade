<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Order;
use App\Models\Wallet;
use App\Services\LedgerService;
use App\Services\OrderBookEngine;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Submit a new limit or market order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'market_id' => 'required|exists:markets,id',
            'side' => 'required|in:buy,sell',
            'type' => 'required|in:limit,market',
            'price' => 'required_if:type,limit|numeric|gt:0',
            'quantity' => 'required|numeric|gt:0',
            'is_demo' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $market = Market::findOrFail($request->market_id);
        $isDemo = $request->boolean('is_demo', false);

        if ($request->quantity < $market->min_order_size) {
            return response()->json([
                'message' => "Order quantity must be at least {$market->min_order_size} {$market->base_currency}.",
            ], 422);
        }

        // SECURITY MIDDLEWARE: Anti-Price-Spoofing Oracle
        $actualPrice = null;
        try {
            $cleanSym = str_replace('/', '', $market->symbol);
            $res = \Illuminate\Support\Facades\Http::timeout(3)->get("https://api.binance.com/api/v3/ticker/price", ['symbol' => $cleanSym]);
            if ($res->ok() && isset($res['price'])) {
                $actualPrice = (float) $res['price'];
                $market->last_price = $actualPrice; // Update local cache
                $market->save();
            }
        } catch (\Exception $e) {
            // Fallback if Binance API is unreachable
        }

        if ($request->type === 'market') {
            if ($request->has('strike_price')) {
                // If the frontend explicitly requested a strike price, ensure it hasn't spoofed/diverged from reality
                $submittedPrice = (float) $request->strike_price;
                if ($actualPrice) {
                    $diffPercent = abs($submittedPrice - $actualPrice) / $actualPrice * 100;
                    if ($diffPercent > 1.0) {
                        return response()->json(['message' => 'Market price divergence too high (Slippage > 1%). Please resubmit your order.'], 400);
                    }
                }
                $price = $submittedPrice;
            } else {
                // If it's a standard market order (like "Close Position"), just use the live oracle price
                $price = $actualPrice ?: ($market->last_price ?: 1);
            }
        } else {
            $price = $request->price;
        }

        $quantity = $request->quantity;

        // Determine wallet & lock amount
        $currencyToLock = $request->side === 'buy' ? $market->quote_currency : $market->base_currency;
        $amountToLock = $request->side === 'buy' ? bcmul((string)$price, (string)$quantity, 8) : (string)$quantity;
        $targetCurrency = $request->side === 'buy' ? $market->base_currency : $market->quote_currency;
        $targetAmount = $request->side === 'buy' ? (string)$quantity : bcmul((string)$price, (string)$quantity, 8);

        // Pre-check balance before transaction
        $sourceWallet = Wallet::firstOrCreate(
            ['user_id' => $user->id, 'currency' => $currencyToLock, 'is_demo' => $isDemo],
            ['available_balance' => ($isDemo && $currencyToLock === 'USDT' ? ($user->demo_balance ?? 10000.0) : 0), 'locked_balance' => 0]
        );

        if ((float) $sourceWallet->available_balance < (float) $amountToLock) {
            $formattedReq = number_format((float)$amountToLock, 4);
            $formattedAvail = number_format((float)$sourceWallet->available_balance, 4);
            return response()->json([
                'message' => "Insufficient {$currencyToLock} available balance. Required: {$formattedReq} {$currencyToLock}, Available: {$formattedAvail} {$currencyToLock}.",
            ], 422);
        }

        // Single atomic transaction: execute spot order directly into user holdings & positions
        return DB::transaction(function () use ($user, $market, $isDemo, $request, $price, $quantity, $currencyToLock, $amountToLock, $targetCurrency, $targetAmount) {
            $sourceWallet = Wallet::where('user_id', $user->id)
                ->where('currency', $currencyToLock)
                ->where('is_demo', $isDemo)
                ->lockForUpdate()
                ->first();

            if ((float) $sourceWallet->available_balance < (float) $amountToLock) {
                return response()->json([
                    'message' => "Insufficient {$currencyToLock} balance to complete order.",
                ], 422);
            }

            // Deduct from source wallet
            $sourceWallet->available_balance = max(0, (float) bcsub((string)$sourceWallet->available_balance, (string)$amountToLock, 8));
            $sourceWallet->save();

            // Credit destination wallet
            $destWallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => $targetCurrency, 'is_demo' => $isDemo],
                ['available_balance' => 0, 'locked_balance' => 0]
            );
            $destWallet = Wallet::where('id', $destWallet->id)->lockForUpdate()->first();
            $destWallet->available_balance = (float) bcadd((string)$destWallet->available_balance, (string)$targetAmount, 8);
            $destWallet->save();

            // Create Order directly as filled
            $order = Order::create([
                'user_id' => $user->id,
                'market_id' => $market->id,
                'side' => $request->side,
                'type' => $request->type,
                'price' => $price,
                'quantity' => $quantity,
                'filled_quantity' => $quantity,
                'status' => 'filled',
                'is_demo' => $isDemo,
            ]);

            // Sync user.demo_balance if demo USDT wallet was modified
            if ($isDemo) {
                $demoUsdt = Wallet::where('user_id', $user->id)->where('currency', 'USDT')->where('is_demo', true)->first();
                if ($demoUsdt) {
                    $user->update(['demo_balance' => (float) $demoUsdt->available_balance]);
                }
            }

            $actionVerb = $request->side === 'buy' ? 'Purchased' : 'Sold';
            $message = "Order executed! {$actionVerb} " . number_format((float)$quantity, 6) . " {$market->base_currency} at $" . number_format((float)$price, 2) . ". Added to your positions & holdings.";

            return response()->json([
                'message' => $message,
                'order' => $order,
                'trades' => [],
            ]);
        });
    }

    /**
     * Cancel an open order.
     */
    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $success = OrderBookEngine::cancelOrder($order);

        if ($success) {
            return response()->json(['message' => 'Order cancelled successfully.']);
        }

        return response()->json(['message' => 'Unable to cancel order.'], 422);
    }

    /**
     * Get user open orders and trade history.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $openOrders = Order::with('market:id,symbol')
            ->where('user_id', $user->id)
            ->whereIn('status', ['open', 'partially_filled'])
            ->latest()
            ->get();

        $orderHistory = Order::with('market:id,symbol')
            ->where('user_id', $user->id)
            ->whereIn('status', ['filled', 'cancelled'])
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'open_orders' => $openOrders,
            'order_history' => $orderHistory,
        ]);
    }
}
