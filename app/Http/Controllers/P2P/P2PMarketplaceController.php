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
        $fiat = $request->query('fiat', 'KES');
        $paymentMethod = $request->query('payment_method', 'all');

        $query = P2PAd::with('user:id,name,email,p2p_merchant_name,p2p_completion_rate,p2p_completed_trades')
            ->where('status', 'active')
            ->where('available_amount', '>', 0)
            ->where('type', $type);

        if ($fiat && $fiat !== 'all') {
            $query->where('fiat', $fiat);
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
        if ($user) {
            $usdtWallet = Wallet::where('user_id', $user->id)
                ->where('currency', 'USDT')
                ->where('is_demo', false)
                ->first();
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
