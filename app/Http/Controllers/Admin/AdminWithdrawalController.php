<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Services\TradingEmailService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminWithdrawalController extends Controller
{
    /**
     * Display pending & historical withdrawals for Admin dashboard.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Withdrawal::with('user:id,name,email,bep20_address')->latest();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $withdrawals = $query->get()->map(function ($w) {
            $w->bscscan_url = $w->tx_hash ? "https://bscscan.com/tx/{$w->tx_hash}" : null;
            $w->address_url = $w->bep20_address ? "https://bscscan.com/address/{$w->bep20_address}" : null;
            return $w;
        });

        $stats = [
            'total_pending' => Withdrawal::where('status', 'pending')->count(),
            'pending_amount' => (float) Withdrawal::where('status', 'pending')->sum('amount'),
            'total_approved' => Withdrawal::where('status', 'approved')->count(),
            'approved_amount' => (float) Withdrawal::where('status', 'approved')->sum('amount'),
            'total_rejected' => Withdrawal::where('status', 'rejected')->count(),
            'total_count' => Withdrawal::count(),
        ];

        return Inertia::render('Admin/Withdrawals', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'currentStatus' => $status ?: 'all',
        ]);
    }

    /**
     * Approve / Complete a pending withdrawal.
     */
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'tx_hash' => ['nullable', 'string'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->first();

            if ($withdrawal->status !== 'pending') {
                throw new Exception('Withdrawal has already been processed.');
            }

            $withdrawal->update([
                'status' => 'approved',
                'tx_hash' => $request->tx_hash ? trim($request->tx_hash) : null,
                'admin_notes' => $request->admin_notes ? trim($request->admin_notes) : 'Approved and completed by admin',
                'approved_at' => now(),
            ]);

            // Dispatch notification email to trader
            try {
                if ($withdrawal->user) {
                    TradingEmailService::sendWithdrawalNotification(
                        $withdrawal->user,
                        (float) $withdrawal->amount,
                        $withdrawal->currency ?: 'USDT',
                        $withdrawal->bep20_address,
                        'Completed / Approved'
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Withdrawal approval email dispatch error: ' . $e->getMessage());
            }
        });

        return redirect()->back()->with('success', "Withdrawal #{$withdrawal->id} of \${$withdrawal->amount} USDT approved and marked as complete.");
    }

    /**
     * Reject a withdrawal and refund the deducted amount back to user's wallet.
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->first();

            if ($withdrawal->status !== 'pending') {
                throw new Exception('Withdrawal has already been processed.');
            }

            // Refund user's available balance in USDT wallet
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency ?: 'USDT')
                ->where('is_demo', false)
                ->lockForUpdate()
                ->first();

            if ($wallet) {
                $wallet->available_balance = (float) bcadd(
                    (string) $wallet->available_balance,
                    (string) $withdrawal->amount,
                    8
                );
                $wallet->save();
            }

            $reasonText = $request->reason ? trim($request->reason) : 'Rejected by admin. Funds refunded to wallet.';

            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $reasonText,
                'approved_at' => now(),
            ]);

            // Dispatch notification email to trader
            try {
                if ($withdrawal->user) {
                    TradingEmailService::sendWithdrawalNotification(
                        $withdrawal->user,
                        (float) $withdrawal->amount,
                        $withdrawal->currency ?: 'USDT',
                        $withdrawal->bep20_address,
                        'Rejected - ' . $reasonText
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Withdrawal rejection email dispatch error: ' . $e->getMessage());
            }
        });

        return redirect()->back()->with('success', "Withdrawal #{$withdrawal->id} rejected. \${$withdrawal->amount} USDT has been refunded to the trader's wallet.");
    }
}
