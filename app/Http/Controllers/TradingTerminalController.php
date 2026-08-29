<?php

namespace App\Http\Controllers;

use App\Models\BinaryOptionContract;
use App\Models\Market;
use App\Models\Order;
use App\Models\Trade;
use App\Models\Wallet;
use App\Services\MarketDataSyncService;
use App\Services\OrderBookEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TradingTerminalController extends Controller
{
    /**
     * Display Main Trading Terminal SPA interface.
     */
    public function index(Request $request, string $symbol = 'BTC/USDT')
    {
        $symbol = str_replace('_', '/', strtoupper($symbol));
        $market = Market::where('symbol', $symbol)->first();

        if (!$market) {
            $market = Market::firstOrCreate(
                ['symbol' => 'BTC/USDT'],
                [
                    'base_currency' => 'BTC',
                    'quote_currency' => 'USDT',
                    'min_order_size' => 0.0001,
                    'price_precision' => 2,
                    'quantity_precision' => 4,
                    'last_price' => 64500.00,
                    'change_24h' => 2.45,
                    'high_24h' => 65200.00,
                    'low_24h' => 63800.00,
                    'volume_24h' => 142.50,
                ]
            );
        }

        $allMarkets = Market::where('status', 'active')->get();

        // Get Order Book Depth from Redis Engine
        $orderBook = OrderBookEngine::getOrderBookDepth($market->id);

        // Fetch hundreds of real live executed market trades from Binance API
        $publicTrades = MarketDataSyncService::fetchRecentPublicTrades($market, 100);

        // Merge with local DB trades if available
        $localTrades = Trade::where('market_id', $market->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'market_id' => $t->market_id,
                    'price' => (float) $t->price,
                    'quantity' => (float) $t->quantity,
                    'side' => $t->side,
                    'timestamp' => $t->created_at->toDateTimeString(),
                ];
            })->toArray();

        $recentTrades = array_merge($localTrades, $publicTrades);

        // Get User Wallets and Open Orders if authenticated
        $userWallets = [];
        $userOrders  = [];

        if ($user = $request->user()) {
            // Ensure demo USDT wallet exists and is synchronized with users.demo_balance
            $demoUsdtWallet = Wallet::where('user_id', $user->id)
                ->where('currency', 'USDT')
                ->where('is_demo', true)
                ->first();
            if (!$demoUsdtWallet) {
                $demoUsdtWallet = Wallet::create([
                    'user_id' => $user->id,
                    'currency' => 'USDT',
                    'available_balance' => $user->demo_balance ?? 10000.00,
                    'locked_balance' => 0,
                    'is_demo' => true,
                ]);
            } else {
                $user->update(['demo_balance' => $demoUsdtWallet->available_balance + $demoUsdtWallet->locked_balance]);
            }

            $userWallets = Wallet::where('user_id', $user->id)->get();
            $userOrders = Order::with('market:id,symbol')
                ->where('user_id', $user->id)
                ->whereIn('status', ['open', 'partially_filled'])
                ->latest()
                ->get();
        }

        return Inertia::render('Trading/Terminal', [
            'currentMarket' => $market,
            'markets' => $allMarkets,
            'orderBook' => $orderBook,
            'recentTrades' => array_slice($recentTrades, 0, 100),
            'userWallets' => $userWallets,
            'userOrders' => $userOrders,
            'custodialAddress' => config('app.bep20_custodial_address', '0x71C7656EC7ab88b098defB751B7401B5f6d8976F'),
            'reverbConfig' => [
                'key' => config('reverb.apps.apps.0.key', env('VITE_REVERB_APP_KEY')),
                'host' => config('reverb.apps.apps.0.options.host', env('VITE_REVERB_HOST', '127.0.0.1')),
                'port' => config('reverb.apps.apps.0.options.port', env('VITE_REVERB_PORT', 8080)),
                'scheme' => config('reverb.apps.apps.0.options.scheme', env('VITE_REVERB_SCHEME', 'http')),
            ],
        ]);
    }

    /**
     * Display My Trades & Orders History Portal.
     */
    public function myTrades(Request $request)
    {
        $user = $request->user();

        // Ensure demo USDT wallet exists and is synchronized with users.demo_balance
        $demoUsdtWallet = Wallet::where('user_id', $user->id)
            ->where('currency', 'USDT')
            ->where('is_demo', true)
            ->first();
        if (!$demoUsdtWallet) {
            Wallet::create([
                'user_id' => $user->id,
                'currency' => 'USDT',
                'available_balance' => $user->demo_balance ?? 10000.00,
                'locked_balance' => 0,
                'is_demo' => true,
            ]);
        } else {
            $user->update(['demo_balance' => $demoUsdtWallet->available_balance + $demoUsdtWallet->locked_balance]);
        }

        $openOrders = Order::with('market:id,symbol')
            ->where('user_id', $user->id)
            ->whereIn('status', ['open', 'partially_filled'])
            ->latest()
            ->get();

        $orderHistory = Order::with('market:id,symbol')
            ->where('user_id', $user->id)
            ->whereIn('status', ['filled', 'cancelled'])
            ->latest()
            ->get();

        $activeOptions = BinaryOptionContract::with('market:id,symbol')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        $settledOptions = BinaryOptionContract::with('market:id,symbol')
            ->where('user_id', $user->id)
            ->whereIn('status', ['win', 'loss'])
            ->latest()
            ->get();

        $wallets = Wallet::where('user_id', $user->id)->get();
        $markets = Market::where('status', 'active')->get();

        return Inertia::render('Trading/MyTrades', [
            'openOrders' => $openOrders,
            'orderHistory' => $orderHistory,
            'activeOptions' => $activeOptions,
            'settledOptions' => $settledOptions,
            'wallets' => $wallets,
            'markets' => $markets,
        ]);
    }

    public function terms()
    {
        return Inertia::render('Legal/Terms');
    }

    public function privacy()
    {
        return Inertia::render('Legal/Privacy');
    }

    public function riskDisclosure()
    {
        return Inertia::render('Legal/RiskDisclosure');
    }
}
