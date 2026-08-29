<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MmfSubscription;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class MmfController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptions = MmfSubscription::where("user_id", $user->id)->orderBy("created_at", "desc")->get();
        $logs = \App\Models\MmfInterestLog::where("user_id", $user->id)->orderBy("created_at", "desc")->get();
        
        return Inertia::render("MonthlyInterests/Index", [
            "subscriptions" => $subscriptions,
            "logs" => $logs,
            "user" => $user,
        ]);
    }

    public function lockFunds(Request $request)
    {
        $request->validate([
            "amount" => "required|numeric|min:10",
            "duration_days" => "required|integer|in:30",
        ]);

        $user = $request->user();
        $amount = (float) $request->amount;
        $apy = 0.05;

        return DB::transaction(function () use ($user, $amount, $request, $apy) {
            $wallet = Wallet::firstOrCreate(
                ["user_id" => $user->id, "currency" => "USDT", "is_demo" => false],
                ["available_balance" => 0, "locked_balance" => 0]
            );

            $wallet = Wallet::where("id", $wallet->id)->lockForUpdate()->first();

            if ((float) $wallet->available_balance < $amount) {
                return back()->withErrors(["amount" => "Insufficient USDT balance to lock."]);
            }

            $wallet->available_balance = (float) bcsub((string)$wallet->available_balance, (string)$amount, 8);
            $wallet->save();

            $expectedInterest = $amount * $apy;
            MmfSubscription::create([
                "user_id" => $user->id,
                "amount" => $amount,
                "expected_interest" => $expectedInterest,
                "locked_at" => now(),
                "unlocks_at" => now()->addDays((int) $request->duration_days),
                "status" => "locked",
            ]);

            return back()->with("message", "Successfully locked $" . number_format($amount, 2) . " USDT for " . $request->duration_days . " days.");
        });
    }
}
