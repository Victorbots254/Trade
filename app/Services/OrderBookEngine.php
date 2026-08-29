<?php

namespace App\Services;

use App\Events\OrderBookUpdated;
use App\Events\TickerUpdated;
use App\Events\TradeExecuted;
use App\Models\Market;
use App\Models\Order;
use App\Models\Trade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class OrderBookEngine
{
    /**
     * Process an incoming order: match against opposite side or add to order book.
     */
    public static function processOrder(Order $order): array
    {
        $market = $order->market;
        $marketId = $market->id;
        $trades = [];

        $remainingQty = (float) bcsub((string)$order->quantity, (string)$order->filled_quantity, 8);
        if ($remainingQty <= 0) {
            return $trades;
        }

        $bidsKey = "orderbook:{$marketId}:bids";
        $asksKey = "orderbook:{$marketId}:asks";
        $detailsKey = "orderbook:{$marketId}:details";

        if ($order->side === 'buy') {
            // Match against asks (lowest price first)
            while ($remainingQty > 0) {
                $bestAskId = null;
                $askPrice = 0.0;
                $askRemaining = 0.0;

                try {
                    $bestAsks = Redis::zrangebyscore($asksKey, '-inf', '+inf', ['limit' => [0, 1]]);
                    if (!empty($bestAsks)) {
                        $bestAskId = $bestAsks[0];
                        $askJson = Redis::hget($detailsKey, "order_{$bestAskId}");
                        if ($askJson) {
                            $askOrderData = json_decode($askJson, true);
                            $askPrice = (float) $askOrderData['price'];
                            $askRemaining = (float) $askOrderData['remaining_quantity'];
                        }
                    }
                } catch (\Throwable $e) {
                    // Redis offline fallback: query open DB ask orders
                    $dbAsk = Order::where('market_id', $marketId)
                        ->where('side', 'sell')
                        ->whereIn('status', ['open', 'partially_filled'])
                        ->where('user_id', '!=', $order->user_id)
                        ->orderBy('price', 'asc')
                        ->first();

                    if ($dbAsk) {
                        $bestAskId = $dbAsk->id;
                        $askPrice = (float) $dbAsk->price;
                        $askRemaining = (float) bcsub((string)$dbAsk->quantity, (string)$dbAsk->filled_quantity, 8);
                    }
                }

                if (!$bestAskId) {
                    break;
                }

                // Limit buy can only match if ask price <= buy price
                if ($order->type === 'limit' && $askPrice > (float)$order->price) {
                    break;
                }

                $matchQty = min($remainingQty, $askRemaining);
                $matchPrice = $askPrice;

                $sellOrder = Order::find($bestAskId);
                if (!$sellOrder) {
                    self::safeRedisZrem($asksKey, $bestAskId);
                    self::safeRedisHdel($detailsKey, "order_{$bestAskId}");
                    continue;
                }

                $trade = Trade::create([
                    'market_id' => $marketId,
                    'buy_order_id' => $order->id,
                    'sell_order_id' => $sellOrder->id,
                    'buyer_id' => $order->user_id,
                    'seller_id' => $sellOrder->user_id,
                    'price' => $matchPrice,
                    'quantity' => $matchQty,
                    'side' => 'buy',
                ]);

                LedgerService::settleTrade($order, $sellOrder, $matchPrice, $matchQty, $trade->id);

                $order->filled_quantity = bcadd((string)$order->filled_quantity, (string)$matchQty, 8);
                $remainingQty = bcsub((string)$remainingQty, (string)$matchQty, 8);
                $order->status = ((float)$remainingQty <= 0) ? 'filled' : 'partially_filled';
                $order->save();

                $sellOrder->filled_quantity = bcadd((string)$sellOrder->filled_quantity, (string)$matchQty, 8);
                $askRemaining = bcsub((string)$askRemaining, (string)$matchQty, 8);

                if ((float)$askRemaining <= 0) {
                    $sellOrder->status = 'filled';
                    self::safeRedisZrem($asksKey, $bestAskId);
                    self::safeRedisHdel($detailsKey, "order_{$bestAskId}");
                } else {
                    $sellOrder->status = 'partially_filled';
                    self::safeRedisHset($detailsKey, "order_{$bestAskId}", json_encode([
                        'id' => $sellOrder->id,
                        'user_id' => $sellOrder->user_id,
                        'side' => 'sell',
                        'price' => (float)$sellOrder->price,
                        'quantity' => (float)$sellOrder->quantity,
                        'remaining_quantity' => (float)$askRemaining,
                    ]));
                }
                $sellOrder->save();

                $market->last_price = $matchPrice;
                if ($market->high_24h == 0 || $matchPrice > $market->high_24h) $market->high_24h = $matchPrice;
                if ($market->low_24h == 0 || $matchPrice < $market->low_24h) $market->low_24h = $matchPrice;
                $market->volume_24h = bcadd((string)$market->volume_24h, (string)$matchQty, 8);
                $market->save();

                $trades[] = $trade;
                self::safeBroadcast(new TradeExecuted($trade));
            }

            if ((float)$remainingQty > 0 && $order->type === 'limit') {
                self::safeRedisZadd($bidsKey, (float)$order->price, $order->id);
                self::safeRedisHset($detailsKey, "order_{$order->id}", json_encode([
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'side' => 'buy',
                    'price' => (float)$order->price,
                    'quantity' => (float)$order->quantity,
                    'remaining_quantity' => (float)$remainingQty,
                ]));
            }
        } else {
            // Sell order: match against bids (highest price first)
            while ($remainingQty > 0) {
                $bestBidId = null;
                $bidPrice = 0.0;
                $bidRemaining = 0.0;

                try {
                    $bestBids = Redis::zrevrangebyscore($bidsKey, '+inf', '-inf', ['limit' => [0, 1]]);
                    if (!empty($bestBids)) {
                        $bestBidId = $bestBids[0];
                        $bidJson = Redis::hget($detailsKey, "order_{$bestBidId}");
                        if ($bidJson) {
                            $bidOrderData = json_decode($bidJson, true);
                            $bidPrice = (float) $bidOrderData['price'];
                            $bidRemaining = (float) $bidOrderData['remaining_quantity'];
                        }
                    }
                } catch (\Throwable $e) {
                    // DB Fallback
                    $dbBid = Order::where('market_id', $marketId)
                        ->where('side', 'buy')
                        ->whereIn('status', ['open', 'partially_filled'])
                        ->where('user_id', '!=', $order->user_id)
                        ->orderBy('price', 'desc')
                        ->first();

                    if ($dbBid) {
                        $bestBidId = $dbBid->id;
                        $bidPrice = (float) $dbBid->price;
                        $bidRemaining = (float) bcsub((string)$dbBid->quantity, (string)$dbBid->filled_quantity, 8);
                    }
                }

                if (!$bestBidId) {
                    break;
                }

                if ($order->type === 'limit' && $bidPrice < (float)$order->price) {
                    break;
                }

                $matchQty = min($remainingQty, $bidRemaining);
                $matchPrice = $bidPrice;

                $buyOrder = Order::find($bestBidId);
                if (!$buyOrder) {
                    self::safeRedisZrem($bidsKey, $bestBidId);
                    self::safeRedisHdel($detailsKey, "order_{$bestBidId}");
                    continue;
                }

                $trade = Trade::create([
                    'market_id' => $marketId,
                    'buy_order_id' => $buyOrder->id,
                    'sell_order_id' => $order->id,
                    'buyer_id' => $buyOrder->user_id,
                    'seller_id' => $order->user_id,
                    'price' => $matchPrice,
                    'quantity' => $matchQty,
                    'side' => 'sell',
                ]);

                LedgerService::settleTrade($buyOrder, $order, $matchPrice, $matchQty, $trade->id);

                $order->filled_quantity = bcadd((string)$order->filled_quantity, (string)$matchQty, 8);
                $remainingQty = bcsub((string)$remainingQty, (string)$matchQty, 8);
                $order->status = ((float)$remainingQty <= 0) ? 'filled' : 'partially_filled';
                $order->save();

                $buyOrder->filled_quantity = bcadd((string)$buyOrder->filled_quantity, (string)$matchQty, 8);
                $bidRemaining = bcsub((string)$bidRemaining, (string)$matchQty, 8);

                if ((float)$bidRemaining <= 0) {
                    $buyOrder->status = 'filled';
                    self::safeRedisZrem($bidsKey, $bestBidId);
                    self::safeRedisHdel($detailsKey, "order_{$bestBidId}");
                } else {
                    $buyOrder->status = 'partially_filled';
                    self::safeRedisHset($detailsKey, "order_{$bestBidId}", json_encode([
                        'id' => $buyOrder->id,
                        'user_id' => $buyOrder->user_id,
                        'side' => 'buy',
                        'price' => (float)$buyOrder->price,
                        'quantity' => (float)$buyOrder->quantity,
                        'remaining_quantity' => (float)$bidRemaining,
                    ]));
                }
                $buyOrder->save();

                $market->last_price = $matchPrice;
                if ($market->high_24h == 0 || $matchPrice > $market->high_24h) $market->high_24h = $matchPrice;
                if ($market->low_24h == 0 || $matchPrice < $market->low_24h) $market->low_24h = $matchPrice;
                $market->volume_24h = bcadd((string)$market->volume_24h, (string)$matchQty, 8);
                $market->save();

                $trades[] = $trade;
                self::safeBroadcast(new TradeExecuted($trade));
            }

            if ((float)$remainingQty > 0 && $order->type === 'limit') {
                self::safeRedisZadd($asksKey, (float)$order->price, $order->id);
                self::safeRedisHset($detailsKey, "order_{$order->id}", json_encode([
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'side' => 'sell',
                    'price' => (float)$order->price,
                    'quantity' => (float)$order->quantity,
                    'remaining_quantity' => (float)$remainingQty,
                ]));
            }
        }

        $depth = self::getOrderBookDepth($marketId);
        self::safeBroadcast(new OrderBookUpdated($market->symbol, $depth));
        self::safeBroadcast(new TickerUpdated($market));

        return $trades;
    }

    public static function cancelOrder(Order $order): bool
    {
        if ($order->status !== 'open' && $order->status !== 'partially_filled') {
            return false;
        }

        $marketId = $order->market_id;
        $bidsKey = "orderbook:{$marketId}:bids";
        $asksKey = "orderbook:{$marketId}:asks";
        $detailsKey = "orderbook:{$marketId}:details";

        if ($order->side === 'buy') {
            self::safeRedisZrem($bidsKey, $order->id);
        } else {
            self::safeRedisZrem($asksKey, $order->id);
        }
        self::safeRedisHdel($detailsKey, "order_{$order->id}");

        $remainingQty = bcsub((string)$order->quantity, (string)$order->filled_quantity, 8);
        $order->status = 'cancelled';
        $order->save();

        $walletCurrency = $order->side === 'buy' ? $order->market->quote_currency : $order->market->base_currency;
        $wallet = \App\Models\Wallet::where('user_id', $order->user_id)
            ->where('currency', $walletCurrency)
            ->where('is_demo', $order->is_demo)
            ->first();
        if ($wallet) {
            $unlockAmount = $order->side === 'buy' ? bcmul((string)$remainingQty, (string)$order->price, 8) : $remainingQty;
            LedgerService::unlockFundsForOrder($wallet, $unlockAmount, $order->id);
            if ($order->is_demo && $walletCurrency === 'USDT') {
                $order->user->update(['demo_balance' => $wallet->available_balance + $wallet->locked_balance]);
            }
        }

        $depth = self::getOrderBookDepth($marketId);
        self::safeBroadcast(new OrderBookUpdated($order->market->symbol, $depth));

        return true;
    }

    public static function getOrderBookDepth(int $marketId, int $limit = 20): array
    {
        $bidsKey = "orderbook:{$marketId}:bids";
        $asksKey = "orderbook:{$marketId}:asks";
        $detailsKey = "orderbook:{$marketId}:details";

        $bidsGrouped = [];
        $asksGrouped = [];

        try {
            $rawBids = Redis::zrevrangebyscore($bidsKey, '+inf', '-inf', ['limit' => [0, 50]]);
            $rawAsks = Redis::zrangebyscore($asksKey, '-inf', '+inf', ['limit' => [0, 50]]);

            foreach ($rawBids as $id) {
                $data = json_decode(Redis::hget($detailsKey, "order_{$id}") ?? '{}', true);
                if (!$data) continue;
                $p = (string) $data['price'];
                $q = (float) $data['remaining_quantity'];
                $bidsGrouped[$p] = ($bidsGrouped[$p] ?? 0) + $q;
            }

            foreach ($rawAsks as $id) {
                $data = json_decode(Redis::hget($detailsKey, "order_{$id}") ?? '{}', true);
                if (!$data) continue;
                $p = (string) $data['price'];
                $q = (float) $data['remaining_quantity'];
                $asksGrouped[$p] = ($asksGrouped[$p] ?? 0) + $q;
            }
        } catch (\Throwable $e) {}

        if (empty($bidsGrouped)) {
            $dbBids = Order::where('market_id', $marketId)->where('side', 'buy')->where('is_demo', false)->whereIn('status', ['open', 'partially_filled'])->orderByDesc('price')->get();
            foreach ($dbBids as $o) {
                $p = (string) $o->price;
                $q = (float) bcsub((string)$o->quantity, (string)$o->filled_quantity, 8);
                $bidsGrouped[$p] = ($bidsGrouped[$p] ?? 0) + $q;
            }
        }

        if (empty($asksGrouped)) {
            $dbAsks = Order::where('market_id', $marketId)->where('side', 'sell')->where('is_demo', false)->whereIn('status', ['open', 'partially_filled'])->orderBy('price')->get();
            foreach ($dbAsks as $o) {
                $p = (string) $o->price;
                $q = (float) bcsub((string)$o->quantity, (string)$o->filled_quantity, 8);
                $asksGrouped[$p] = ($asksGrouped[$p] ?? 0) + $q;
            }
        }

        $formattedBids = [];
        foreach ($bidsGrouped as $price => $totalQty) {
            $formattedBids[] = ['price' => (float)$price, 'quantity' => $totalQty];
        }
        usort($formattedBids, fn($a, $b) => $b['price'] <=> $a['price']);

        $formattedAsks = [];
        foreach ($asksGrouped as $price => $totalQty) {
            $formattedAsks[] = ['price' => (float)$price, 'quantity' => $totalQty];
        }
        usort($formattedAsks, fn($a, $b) => $a['price'] <=> $b['price']);

        return [
            'bids' => array_slice($formattedBids, 0, $limit),
            'asks' => array_slice($formattedAsks, 0, $limit),
        ];
    }

    private static function safeRedisZadd($key, $score, $member): void
    {
        try { Redis::zadd($key, $score, $member); } catch (\Throwable $e) {}
    }

    private static function safeRedisZrem($key, $member): void
    {
        try { Redis::zrem($key, $member); } catch (\Throwable $e) {}
    }

    private static function safeRedisHset($key, $field, $value): void
    {
        try { Redis::hset($key, $field, $value); } catch (\Throwable $e) {}
    }

    private static function safeRedisHdel($key, $field): void
    {
        try { Redis::hdel($key, $field); } catch (\Throwable $e) {}
    }

    private static function safeBroadcast($event): void
    {
        try { broadcast($event)->toOthers(); } catch (\Throwable $e) {}
    }
}
