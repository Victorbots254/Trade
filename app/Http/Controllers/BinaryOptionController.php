<?php

namespace App\Http\Controllers;

use App\Models\BinaryOptionContract;
use App\Models\Market;
use App\Models\User;
use App\Models\Wallet;
use App\Services\LedgerService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BinaryOptionController extends Controller
{
    /**
     * Display Time-Expiry Options Terminal interface.
     */
    public function index(Request $request, string $symbol = 'BTC/USDT')
    {
        $symbol = str_replace('_', '/', strtoupper($symbol));
        $market = Market::where('symbol', $symbol)->first() ?: Market::first();
        $markets = Market::where('status', 'active')->get();

        $userWallets = [];
        $activeContracts = [];
        $historyContracts = [];

        if ($user = $request->user()) {
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

            $userWallets = Wallet::where('user_id', $user->id)->get();
            $activeContracts = BinaryOptionContract::with('market:id,symbol')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->get();
            $historyContracts = BinaryOptionContract::with('market:id,symbol')
                ->where('user_id', $user->id)
                ->whereIn('status', ['win', 'loss'])
                ->latest()
                ->limit(20)
                ->get();
        }

        return Inertia::render('Trading/OptionsTerminal', [
            'currentMarket' => $market,
            'markets' => $markets,
            'userWallets' => $userWallets,
            'activeContracts' => $activeContracts,
            'historyContracts' => $historyContracts,
        ]);
    }

    /**
     * Create a new Time-Expiry Binary Option Contract (Higher / Lower).
     */
    public function store(Request $request)
    {
        $request->validate([
            'market_id' => 'required|exists:markets,id',
            'direction' => 'required|in:higher,lower',
            'amount' => 'required|numeric|min:1.00',
            'duration_seconds' => 'required|integer|in:60,300,900,1800,3600',
            'is_demo' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $market = Market::findOrFail($request->market_id);
        $amount = (float) $request->amount;
        $isDemo = $request->boolean('is_demo', false);
        $payoutRate = 0.88; // 88% profit payout
        $payoutAmount = $amount * (1 + $payoutRate);
        // SECURITY MIDDLEWARE: Anti-Price-Spoofing Oracle
        // Ensure the frontend-submitted strike price is accurate and not tampered with.
        $submittedPrice = $request->has('strike_price') ? (float) $request->strike_price : (float) $market->last_price;
        try {
            $cleanSym = str_replace('/', '', $market->symbol);
            $res = \Illuminate\Support\Facades\Http::timeout(3)->get("https://api.binance.com/api/v3/ticker/price", ['symbol' => $cleanSym]);
            if ($res->ok() && isset($res['price'])) {
                $actualPrice = (float) $res['price'];
                $market->last_price = $actualPrice; // Update local cache
                
                // Allow up to 0.5% slippage (crypto moves fast, but prevents massive spoofing like $10 for BTC)
                $diffPercent = abs($submittedPrice - $actualPrice) / $actualPrice * 100;
                if ($diffPercent > 0.5) {
                    return response()->json(['message' => 'Price rejected due to high slippage. Please resubmit your option.'], 400);
                }
            }
        } catch (\Exception $e) {
            // If Binance API fails, rely on the locally provided price but we could enforce stricter checks if needed.
        }

        $contract = DB::transaction(function () use ($user, $market, $request, $amount, $payoutRate, $payoutAmount, $isDemo, $submittedPrice) {
            if ($isDemo) {
                // Deduct & Lock Demo USDT Wallet Funds
                $demoWallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => true],
                    ['available_balance' => 10000.00, 'locked_balance' => 0]
                );

                $demoWallet = Wallet::where('id', $demoWallet->id)->lockForUpdate()->first();

                if ((float) $demoWallet->available_balance < $amount) {
                    throw new Exception("Insufficient Demo Practice balance ($" . number_format($demoWallet->available_balance, 2) . "). Click 'Reset $10k' to refill demo practice funds.");
                }

                // Lock funds for demo option contract
                $demoWallet->available_balance = (float) bcsub((string)$demoWallet->available_balance, (string)$amount, 8);
                $demoWallet->locked_balance = (float) bcadd((string)$demoWallet->locked_balance, (string)$amount, 8);
                $demoWallet->save();

                // Keep users.demo_balance in sync
                $userObj = User::where('id', $user->id)->lockForUpdate()->first();
                if ($userObj) {
                    $userObj->demo_balance = max(0, $demoWallet->available_balance + $demoWallet->locked_balance);
                    $userObj->save();
                }

                return BinaryOptionContract::create([
                    'user_id' => $user->id,
                    'market_id' => $market->id,
                    'direction' => $request->direction,
                    'entry_price' => $submittedPrice,
                    'investment_amount' => $amount,
                    'payout_rate' => $payoutRate,
                    'payout_amount' => $payoutAmount,
                    'duration_seconds' => (int) $request->duration_seconds,
                    'expires_at' => now()->addSeconds((int)$request->duration_seconds),
                    'status' => 'active',
                    'is_demo' => true,
                ]);
            } else {
                // Deduct & Lock Real Live USDT Wallet Funds
                $usdtWallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id, 'currency' => 'USDT'],
                    ['available_balance' => 0, 'locked_balance' => 0]
                );

                $usdtWallet = Wallet::where('id', $usdtWallet->id)->lockForUpdate()->first();

                if ((float) $usdtWallet->available_balance < $amount) {
                    throw new Exception("Insufficient Real USDT balance ($" . number_format($usdtWallet->available_balance, 2) . "). Deposit real funds to place live contracts.");
                }

                // Lock funds for real option contract
                $usdtWallet->available_balance = (float) bcsub((string)$usdtWallet->available_balance, (string)$amount, 8);
                $usdtWallet->locked_balance = (float) bcadd((string)$usdtWallet->locked_balance, (string)$amount, 8);
                $usdtWallet->save();

                return BinaryOptionContract::create([
                    'user_id' => $user->id,
                    'market_id' => $market->id,
                    'direction' => $request->direction,
                    'entry_price' => $submittedPrice,
                    'investment_amount' => $amount,
                    'payout_rate' => $payoutRate,
                    'payout_amount' => $payoutAmount,
                    'duration_seconds' => (int) $request->duration_seconds,
                    'expires_at' => now()->addSeconds((int)$request->duration_seconds),
                    'status' => 'active',
                    'is_demo' => false,
                ]);
            }
        });

        return response()->json([
            'message' => $isDemo ? '🎮 Demo Practice Contract initiated!' : '🟢 Real Live Option Contract initiated!',
            'contract' => $contract->load('market:id,symbol'),
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Settle an expired Option Contract automatically (respecting Admin Trading Control Mode).
     */
    public function settle(Request $request, BinaryOptionContract $contract)
    {
        if ($contract->status !== 'active') {
            return response()->json(['message' => 'Contract already settled']);
        }

        $request->validate([
            'strike_price' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($contract, $request) {
            $contract = BinaryOptionContract::where('id', $contract->id)->lockForUpdate()->first();
            if ($contract->status !== 'active') return;

            $user = $contract->user;
            $mode = $user ? $user->trading_outcome_mode : 'fair_market';

            $entryPrice = (float) $contract->entry_price;
            $direction = $contract->direction;
            $strikePrice = (float) ($request->strike_price ?? $entryPrice);
            $win = false;

            $precision = $contract->market->price_precision ?? 2;
            $offset = 1.0 / pow(10, min($precision, 4));

            // Admin Trading Outcome Override Engine
            if ($mode === 'force_win') {
                $win = true;
                if ($direction === 'higher') {
                    $strikePrice = $strikePrice > $entryPrice ? $strikePrice : ($entryPrice + $offset);
                } else {
                    $strikePrice = $strikePrice < $entryPrice ? $strikePrice : ($entryPrice - $offset);
                }
            } else if ($mode === 'force_loss') {
                $win = false;
                if ($direction === 'higher') {
                    $strikePrice = $strikePrice < $entryPrice ? $strikePrice : ($entryPrice - $offset);
                } else {
                    $strikePrice = $strikePrice > $entryPrice ? $strikePrice : ($entryPrice + $offset);
                }
            } else {
                // Fair Market Execution Mode
                if ($direction === 'higher' && $strikePrice > $entryPrice) {
                    $win = true;
                } else if ($direction === 'lower' && $strikePrice < $entryPrice) {
                    $win = true;
                }
            }

            // Find the matching wallet (real vs demo!)
            $usdtWallet = Wallet::where('user_id', $contract->user_id)
                ->where('currency', 'USDT')
                ->where('is_demo', $contract->is_demo)
                ->lockForUpdate()
                ->first();

            if ($usdtWallet) {
                // Unlock locked investment amount
                $usdtWallet->locked_balance = max(0, (float) bcsub((string)$usdtWallet->locked_balance, (string)$contract->investment_amount, 8));

                if ($win) {
                    // Credit payout amount to available USDT balance
                    $usdtWallet->available_balance = (float) bcadd((string)$usdtWallet->available_balance, (string)$contract->payout_amount, 8);
                }
                $usdtWallet->save();

                // Keep users.demo_balance in sync if it is a demo contract
                if ($contract->is_demo) {
                    $userObj = User::where('id', $contract->user_id)->lockForUpdate()->first();
                    if ($userObj) {
                        $userObj->demo_balance = max(0, $usdtWallet->available_balance + $usdtWallet->locked_balance);
                        $userObj->save();
                    }
                }
            }

            $contract->update([
                'strike_price' => (float) number_format($strikePrice, 2, '.', ''),
                'status' => $win ? 'win' : 'loss',
            ]);
        });

        $contract = $contract->fresh()->load('market:id,symbol');
        $profit = $contract->payout_amount - $contract->investment_amount;
        $formattedProfit = number_format($profit, 2);
        $formattedPayout = number_format($contract->payout_amount, 2);
        $formattedInvest = number_format($contract->investment_amount, 2);

        $prefix = $contract->is_demo ? '🎮 Demo Practice Contract' : '🟢 Real Option Contract';

        $notificationMessage = $contract->status === 'win'
            ? "🎉 {$prefix} WON! +$" . $formattedProfit . " Profit Credited ($" . $formattedPayout . " Total Payout)"
            : "📉 {$prefix} Expired Out of Money ($" . $formattedInvest . ").";

        return response()->json([
            'message' => $notificationMessage,
            'contract' => $contract,
        ]);
    }
}
