<?php

namespace App\Http\Controllers;

use App\Events\NewDepositPendingApproval;
use App\Jobs\SendSupportDepositNotificationJob;
use App\Models\Deposit;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DepositController extends Controller
{
    /**
     * Store new BEP-20 deposit request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|in:USDT,BTC,ETH,BNB',
            'amount' => 'required|numeric|min:0.0001',
            'tx_hash' => 'required|string|unique:deposits,tx_hash|max:100',
            'receipt' => 'nullable|image|max:5120', // Max 5MB
        ], [
            'tx_hash.unique' => 'This Transaction Hash (TxHash) has already been submitted for verification.',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $deposit = Deposit::create([
            'user_id' => $request->user()->id,
            'currency' => $request->currency,
            'amount' => $request->amount,
            'tx_hash' => $request->tx_hash,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);

        // Ensure user wallet exists
        Wallet::firstOrCreate(
            ['user_id' => $request->user()->id, 'currency' => $request->currency],
            ['available_balance' => 0, 'locked_balance' => 0]
        );

        // Dispatch real-time admin WebSocket alert
        broadcast(new NewDepositPendingApproval($deposit));

        // Dispatch Telegram / Discord notification job
        SendSupportDepositNotificationJob::dispatch($deposit);

        return response()->json([
            'message' => 'Deposit submitted successfully! Pending verification by support staff.',
            'deposit' => $deposit,
        ]);
    }

    /**
     * Get current user's deposits list & wallet balances.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $deposits = Deposit::where('user_id', $user->id)
            ->latest()
            ->get();

        $wallets = Wallet::where('user_id', $user->id)->get();

        return response()->json([
            'deposits' => $deposits,
            'wallets' => $wallets,
        ]);
    }
}
