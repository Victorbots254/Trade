<?php

namespace App\Http\Controllers\P2P;

use App\Http\Controllers\Controller;
use App\Models\P2PAd;
use App\Models\P2POrder;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class P2PMarketplaceController extends Controller
{
    /**
     * Display P2P Marketplace (Browse Buy / Sell Ads).
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'sell'); // 'sell' = user wants to BUY usdt; 'buy' = user wants to SELL usdt
        $fiat = $request->query('fiat', 'all'); // 'all', 'USDT', 'USD', 'KES'
        $paymentMethod = $request->query('payment_method', 'all');

        $query = P2PAd::with('user:id,name,email,p2p_merchant_name,p2p_completion_rate,p2p_completed_trades')
            ->where('status', 'active')
            ->where('available_amount', '>', 0)
            ->where('type', $type);

        if ($fiat && $fiat !== 'all') {
            if (in_array(strtoupper($fiat), ['USDT', 'USD'])) {
                $query->whereIn('fiat', ['USD', 'USDT']);
            } else {
                $query->where('fiat', strtoupper($fiat));
            }
        }

        if ($paymentMethod && $paymentMethod !== 'all') {
            $query->whereJsonContains('payment_methods', $paymentMethod);
        }

        // Sort by best price: For user buying (merchant 'sell'), lowest price first. For user selling (merchant 'buy'), highest price first.
        if ($type === 'sell') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('price', 'desc');
        }

        $ads = $query->paginate(20)->withQueryString();

        $user = $request->user();
        $usdtWallet = null;
        $myActiveOrders = [];
        if ($user) {
            $usdtWallet = Wallet::where('user_id', $user->id)
                ->where('currency', 'USDT')
                ->where('is_demo', false)
                ->first();

            $myActiveOrders = P2POrder::with(['buyer:id,name', 'seller:id,name', 'ad'])
                ->where(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                })
                ->whereIn('status', ['pending_payment', 'paid', 'disputed'])
                ->latest()
                ->get();
        }

        // Active count aggregates for fast switcher badges
        $sellAdsCount = P2PAd::where('status', 'active')->where('available_amount', '>', 0)->where('type', 'sell')->count();
        $buyAdsCount = P2PAd::where('status', 'active')->where('available_amount', '>', 0)->where('type', 'buy')->count();
        $usdtAdsCount = P2PAd::where('status', 'active')->where('available_amount', '>', 0)->whereIn('fiat', ['USD', 'USDT'])->count();
        $kesAdsCount = P2PAd::where('status', 'active')->where('available_amount', '>', 0)->where('fiat', 'KES')->count();

        // Get current market rate for KES
        $marketRateKes = (float) \App\Services\MegaPayService::getExchangeRate();
        if ($marketRateKes <= 0) {
            $marketRateKes = 129.50;
        }

        return Inertia::render('P2P/Index', [
            'ads' => $ads,
            'filters' => [
                'type' => $type,
                'fiat' => $fiat,
                'payment_method' => $paymentMethod,
            ],
            'user' => $user,
            'usdtBalance' => $usdtWallet ? (float) $usdtWallet->available_balance : 0.00,
            'myActiveOrders' => $myActiveOrders,
            'stats' => [
                'sellAdsCount' => $sellAdsCount,
                'buyAdsCount' => $buyAdsCount,
                'usdtAdsCount' => $usdtAdsCount,
                'kesAdsCount' => $kesAdsCount,
                'marketRateKes' => $marketRateKes,
            ],
        ]);
    }

    /**
     * Get live market rate for P2P trading.
     */
    public function getMarketPrice(Request $request)
    {
        $fiat = strtoupper($request->query('fiat', 'USDT'));

        if ($fiat === 'KES') {
            $rate = (float) \App\Services\MegaPayService::getExchangeRate();
            if ($rate <= 0) $rate = 129.50;
            return response()->json([
                'fiat' => 'KES',
                'price' => $rate,
                'formatted' => number_format($rate, 2) . ' KES',
                'label' => "Live Market Price (1 USDT = {$rate} KES)",
            ]);
        }

        return response()->json([
            'fiat' => 'USDT',
            'price' => 1.00,
            'formatted' => '$1.00 USDT',
            'label' => 'Standard Stablecoin Peg (1 USDT = $1.00)',
        ]);
    }

    /**
     * Get user's P2P trade history.
     */
    public function myOrders(Request $request)
    {
        $user = $request->user();

        $orders = P2POrder::with(['ad', 'buyer:id,name,email,p2p_merchant_name', 'seller:id,name,email,p2p_merchant_name'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('P2P/OrdersList', [
            'orders' => $orders,
            'user' => $user,
        ]);
    }
}
