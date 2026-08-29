<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\Wallet;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render("Wallet/Withdraw", [
            "user" => $request->user(),
            "withdrawals" => Withdrawal::where("user_id", $request->user()->id)->latest()->get(),
            "wallets" => Wallet::where("user_id", $request->user()->id)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "amount" => "required|numeric|min:10",
        ]);

        $user = $request->user();
        if (empty($user->bep20_address)) {
            return back()->withErrors(["bep20_address" => "You must link a BEP20 address before withdrawing."]);
        }

        $amount = (float) $request->amount;

        return DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::firstOrCreate(
                ["user_id" => $user->id, "currency" => "USDT", "is_demo" => false],
                ["available_balance" => 0, "locked_balance" => 0]
            );

            $wallet = Wallet::where("id", $wallet->id)->lockForUpdate()->first();

            if ((float) $wallet->available_balance < $amount) {
                return back()->withErrors(["amount" => "Insufficient available USDT balance."]);
            }

            // Deduct funds immediately upon withdrawal request
            $wallet->available_balance = (float) bcsub((string)$wallet->available_balance, (string)$amount, 8);
            $wallet->save();

            Withdrawal::create([
                "user_id" => $user->id,
                "currency" => "USDT",
                "amount" => $amount,
                "bep20_address" => $user->bep20_address,
                "status" => "pending",
            ]);

            return back()->with("message", "Withdrawal request for $" . number_format($amount, 2) . " submitted successfully.");
        });
    }
}
