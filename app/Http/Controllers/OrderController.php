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
        $submittedPrice = $request->type === 'market' ? ($request->has('strike_price') ? $request->strike_price : ($market->last_price ?: 1)) : $request->price;
        
        // SECURITY MIDDLEWARE: Anti-Price-Spoofing Oracle
        try {
            $cleanSym = str_replace('/', '', $market->symbol);
            $res = \Illuminate\Support\Facades\Http::timeout(3)->get("https://api.binance.com/api/v3/ticker/price", ['symbol' => $cleanSym]);
            if ($res->ok() && isset($res['price'])) {
                $actualPrice = (float) $res['price'];
                $market->last_price = $actualPrice; // Update local cache
                
                // If it's a market order, the submitted price MUST be within 1% of the actual price to prevent spoofing
                if ($request->type === 'market') {
                    $diffPercent = abs($submittedPrice - $actualPrice) / $actualPrice * 100;
                    if ($diffPercent > 1.0) {
                        return response()->json(['message' => 'Market price divergence too high (Slippage > 1%). Please resubmit your order.'], 400);
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback if Binance API is unreachable
        }

        $price = $submittedPrice;
        $quantity = $request->quantity;

        // Determine wallet & lock amount
        $currencyToLock = $request->side === 'buy' ? $market->quote_currency : $market->base_currency;
        $amountToLock = $request->side === 'buy' ? bcmul((string)$price, (string)$quantity, 8) : (string)$quantity;

        DB::beginTransaction();
        try {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => $currencyToLock, 'is_demo' => $isDemo],
                ['available_balance' => 0, 'locked_balance' => 0]
            );

            // Lock funds using double-entry ledger service
            LedgerService::lockFundsForOrder($wallet, $amountToLock, 0);

            $order = Order::create([
                'user_id' => $user->id,
                'market_id' => $market->id,
                'side' => $request->side,
                'type' => $request->type,
                'price' => $price,
                'quantity' => $quantity,
                'filled_quantity' => 0,
                'status' => 'open',
                'is_demo' => $isDemo,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // If this is a demo order, execute locally and do not broadcast to public orderbooks
        if ($isDemo) {
            $shouldFill = false;
            if ($request->type === 'market') {
                $shouldFill = true;
            } else {
                if ($request->side === 'buy') {
                    $shouldFill = ($price >= $market->last_price);
                } else {
                    $shouldFill = ($price <= $market->last_price);
                }
            }

            if ($shouldFill) {
                DB::beginTransaction();
                try {
                    $targetCurrency = $request->side === 'buy' ? $market->base_currency : $market->quote_currency;
                    $targetAmount = $request->side === 'buy' ? $quantity : bcmul((string)$price, (string)$quantity, 8);

                    $sourceWallet = Wallet::where('user_id', $user->id)
                        ->where('currency', $currencyToLock)
                        ->where('is_demo', true)
                        ->first();

                    $destWallet = Wallet::firstOrCreate(
                        ['user_id' => $user->id, 'currency' => $targetCurrency, 'is_demo' => true],
                        ['available_balance' => 0, 'locked_balance' => 0]
                    );

                    // Unlock locked balance from source wallet
                    $sourceWallet->locked_balance = bcsub((string)$sourceWallet->locked_balance, (string)$amountToLock, 8);
                    $sourceWallet->save();

                    // Credit destination wallet
                    $destWallet->available_balance = bcadd((string)$destWallet->available_balance, (string)$targetAmount, 8);
                    $destWallet->save();

                    $order->filled_quantity = $quantity;
                    $order->status = 'filled';
                    $order->save();

                    // Sync user.demo_balance if USDT wallet is modified
                    if ($currencyToLock === 'USDT') {
                        $user->update(['demo_balance' => $sourceWallet->available_balance + $sourceWallet->locked_balance]);
                    }
                    if ($targetCurrency === 'USDT') {
                        $user->update(['demo_balance' => $destWallet->available_balance + $destWallet->locked_balance]);
                    }

                    DB::commit();
                } catch (\Exception $ex) {
                    DB::rollBack();
                }
            }

            return response()->json([
                'message' => $order->status === 'filled' ? 'Demo order filled instantly!' : 'Demo limit order placed.',
                'order' => $order->fresh(),
                'trades' => [],
            ]);
        }

        // Process matching engine in Redis
        $trades = OrderBookEngine::processOrder($order);

        return response()->json([
            'message' => 'Order submitted successfully!',
            'order' => $order->fresh(),
            'trades' => $trades,
        ]);
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
