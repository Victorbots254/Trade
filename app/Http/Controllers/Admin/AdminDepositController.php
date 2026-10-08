<?php

namespace App\Http\Controllers\Admin;

use App\Events\DepositApprovedEvent;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Wallet;
use App\Services\LedgerService;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDepositController extends Controller
{
    /**
     * Display pending & historical deposits for Admin dashboard.
     */
    public function index()
    {
        $deposits = Deposit::with('user:id,name,email')
            ->latest()
            ->get()
            ->map(function ($d) {
                $d->bscscan_url = "https://bscscan.com/tx/{$d->tx_hash}";
                return $d;
            });

        $custodialAddress = Setting::get(
            'custodial_bep20_address',
            config('app.bep20_custodial_address', '0x71C7656EC7ab88b098defB751B7401B5f6d8976F')
        );
        $depositQrImage = Setting::get('deposit_qr_image');

        return Inertia::render('Admin/Deposits', [
            'deposits' => $deposits,
            'custodial_address' => $custodialAddress,
            'deposit_qr_image' => $depositQrImage,
        ]);
    }

    /**
     * Update deposit custodial address & custom QR code image.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'custodial_address' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ], [
            'custodial_address.regex' => 'Custodial address must be a valid Binance BEP-20 address (starts with 0x, 42 characters long).',
        ]);

        if ($request->filled('custodial_address')) {
            Setting::set('custodial_bep20_address', trim($request->custodial_address));
        }

        if ($request->hasFile('qr_image')) {
            $path = $request->file('qr_image')->store('deposit_qr', 'public');
            Setting::set('deposit_qr_image', '/storage/' . $path);
        }

        return redirect()->back()->with('success', 'Deposit QR code and address updated successfully!');
    }

    /**
     * Approve a pending deposit with atomic database locks and double-entry ledger credit.
     */
    public function approve(Request $request, Deposit $deposit)
    {
        $adminUser = $request->user();

        DB::transaction(function () use ($deposit, $adminUser) {
            $deposit = Deposit::where('id', $deposit->id)->lockForUpdate()->first();

            if ($deposit->status !== 'pending') {
                throw new Exception('Deposit has already been processed.');
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $deposit->user_id, 'currency' => $deposit->currency],
                ['available_balance' => 0, 'locked_balance' => 0]
            );

            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            // Record double-entry ledger entry and credit wallet
            LedgerService::recordDepositCredit($wallet, $deposit->amount, $deposit->id);

            $deposit->update([
                'status' => 'approved',
                'approved_by' => $adminUser->id,
                'approved_at' => now(),
            ]);

            // Broadcast real-time event to user private channel
            broadcast(new DepositApprovedEvent($deposit, $wallet));
        });

        return redirect()->back()->with('success', "Deposit #{$deposit->id} approved and credited successfully.");
    }

    /**
     * Reject a pending deposit with mandatory reason.
     */
    public function reject(Request $request, Deposit $deposit)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:255',
        ]);

        if ($deposit->status !== 'pending') {
            return redirect()->back()->with('error', 'Deposit has already been processed.');
        }

        $deposit->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', "Deposit #{$deposit->id} has been rejected.");
    }
}
