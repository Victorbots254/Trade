<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\P2PAd;
use App\Models\Withdrawal;
use Carbon\Carbon;

echo "1. Updating username for michaelkairithia3@gmail.com to JamesKe...\n";

// Release JamesKE from any dummy trader first to prevent unique constraint collision
User::where('username', 'JamesKE')->where('email', '!=', 'michaelkairithia3@gmail.com')->update(['username' => 'Trader13_KE']);
User::where('username', 'JamesKe')->where('email', '!=', 'michaelkairithia3@gmail.com')->update(['username' => 'Trader13_James']);

$user3 = User::where('email', 'michaelkairithia3@gmail.com')->first();
if ($user3) {
    $user3->username = 'JamesKe';
    $user3->p2p_merchant_name = 'JamesKe';
    $user3->is_p2p_merchant = true;
    if (empty($user3->p2p_payment_details)) {
        $user3->p2p_payment_details = [
            'mpesa' => [
                'type' => 'phone',
                'phone' => '+254712345678',
                'account_name' => 'James Ke',
            ],
            'bank' => [
                'bank_name' => 'Equity Bank',
                'account_number' => '0110192837465',
                'account_name' => 'James Ke',
            ],
        ];
    }
    $user3->p2p_completion_rate = 100.00;
    $user3->p2p_completed_trades = 5420;
    $user3->p2p_rating = 5.00;
    $user3->save();
    echo "✓ User #{$user3->id} (michaelkairithia3@gmail.com) username updated to: {$user3->username}\n";

    // Re-assign or create Premier Top Trader P2P Ads for JamesKe
    P2PAd::where('is_top_trader', true)->where('is_dummy', false)->update(['user_id' => $user3->id]);

    P2PAd::updateOrCreate([
        'user_id' => $user3->id,
        'type' => 'sell',
        'fiat' => 'KES',
    ], [
        'asset' => 'USDT',
        'price' => 129.20,
        'total_amount' => 15000.00,
        'available_amount' => 14500.00,
        'min_limit' => 500.00,
        'max_limit' => 500000.00,
        'payment_methods' => ['mpesa', 'bank_transfer'],
        'terms' => '⭐ Verified Premier Desk · 100% Rating · Instant Safaricom M-Pesa release in < 2 mins.',
        'auto_reply' => 'Hello! Please send M-Pesa payment and click Payment Sent. Fast release guaranteed.',
        'time_limit_minutes' => 15,
        'status' => 'active',
        'is_dummy' => false,
        'is_limited' => false,
        'is_top_trader' => true,
    ]);

    P2PAd::updateOrCreate([
        'user_id' => $user3->id,
        'type' => 'buy',
        'fiat' => 'KES',
    ], [
        'asset' => 'USDT',
        'price' => 129.00,
        'total_amount' => 10000.00,
        'available_amount' => 9800.00,
        'min_limit' => 500.00,
        'max_limit' => 400000.00,
        'payment_methods' => ['mpesa', 'bank_transfer'],
        'terms' => '⭐ Verified Premier Buyer · Instant payment to your Safaricom M-Pesa line.',
        'auto_reply' => 'Hello, please confirm your Safaricom M-Pesa phone number in chat.',
        'time_limit_minutes' => 15,
        'status' => 'active',
        'is_dummy' => false,
        'is_limited' => false,
        'is_top_trader' => true,
    ]);
    echo "✓ P2P Top Trader ads active for JamesKe ({$user3->email})\n";
} else {
    echo "! User michaelkairithia3@gmail.com not found!\n";
}

// Remove Mikeshi07 name completely from user 1 if present
$user1 = User::where('email', 'michaelkairithia@gmail.com')->first();
if ($user1 && ($user1->username === 'Mikeshi07' || $user1->p2p_merchant_name === 'Mikeshi07')) {
    $user1->username = 'MichaelK_KE';
    $user1->p2p_merchant_name = 'Michael Kairithia';
    $user1->is_p2p_merchant = false;
    $user1->save();
    P2PAd::where('user_id', $user1->id)->delete();
    echo "✓ Cleared legacy Mikeshi07 from user 1.\n";
}

echo "2. Spreading withdrawal dates from March 2026 to October 2026...\n";

// Distribute dates for ALL existing withdrawals from March 2026 to October 2026
$allWithdrawals = Withdrawal::orderBy('id', 'asc')->get();
$total = $allWithdrawals->count();

$startDate = Carbon::create(2026, 3, 1, 9, 0, 0); // March 1, 2026
$endDate = Carbon::create(2026, 10, 14, 8, 0, 0); // October 14, 2026
$totalHours = $startDate->diffInHours($endDate);

foreach ($allWithdrawals as $idx => $w) {
    if ($w->status === 'pending') {
        // Pending withdrawals: placed recently (within last 12-24 hours)
        $created = Carbon::create(2026, 10, 14, rand(1, 8), rand(10, 55), 0);
        $w->created_at = $created;
        $w->approved_at = null;
        $w->paid_at = null;
    } else {
        // Distribute completed / rejected from March onwards
        $fraction = $idx / max(1, $total);
        $hoursToAdd = (int) ($fraction * $totalHours);
        $created = (clone $startDate)->addHours($hoursToAdd)->addMinutes(rand(5, 55));
        
        $w->created_at = $created;
        if ($w->status === 'approved') {
            $approved = (clone $created)->addMinutes(rand(12, 45));
            $w->approved_at = $approved;
            $w->paid_at = (clone $approved)->addMinutes(rand(2, 15));
        } else if ($w->status === 'rejected') {
            $w->approved_at = (clone $created)->addMinutes(rand(15, 60));
            $w->paid_at = null;
        }
    }
    $w->save();
}

echo "✓ Updated {$total} withdrawal timestamps across March – October 2026.\n";

// 3. Ensure michaelkairithia3@gmail.com has rich withdrawal history spanning March to October
if ($user3) {
    Withdrawal::where('user_id', $user3->id)->delete();

    $historyData = [
        ['amount' => 50.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-03-12 14:22:00', 'phone' => '+254712345678'],
        ['amount' => 85.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-03-28 10:15:00', 'phone' => '+254712345678'],
        ['amount' => 120.00, 'method' => 'bep20', 'status' => 'approved', 'date' => '2026-04-14 16:40:00', 'phone' => null],
        ['amount' => 150.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-05-02 09:30:00', 'phone' => '+254712345678'],
        ['amount' => 200.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-05-25 18:05:00', 'phone' => '+254712345678'],
        ['amount' => 90.00, 'method' => 'mpesa', 'status' => 'rejected', 'date' => '2026-06-11 11:20:00', 'phone' => '+254712345678'],
        ['amount' => 240.00, 'method' => 'bep20', 'status' => 'approved', 'date' => '2026-06-29 13:50:00', 'phone' => null],
        ['amount' => 310.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-07-18 15:10:00', 'phone' => '+254712345678'],
        ['amount' => 175.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-08-04 12:00:00', 'phone' => '+254712345678'],
        ['amount' => 280.00, 'method' => 'bep20', 'status' => 'approved', 'date' => '2026-08-27 17:35:00', 'phone' => null],
        ['amount' => 350.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-09-15 10:45:00', 'phone' => '+254712345678'],
        ['amount' => 420.00, 'method' => 'mpesa', 'status' => 'approved', 'date' => '2026-10-02 16:20:00', 'phone' => '+254712345678'],
        ['amount' => 160.00, 'method' => 'mpesa', 'status' => 'pending', 'date' => '2026-10-14 07:15:00', 'phone' => '+254712345678'],
    ];

    foreach ($historyData as $item) {
        $created = Carbon::parse($item['date']);
        $amt = (float) $item['amount'];
        $amtKes = round($amt * 129.50, 2);

        $w = new Withdrawal();
        $w->user_id = $user3->id;
        $w->currency = 'USDT';
        $w->amount = $amt;
        $w->payment_method = $item['method'];
        $w->bep20_address = $item['method'] === 'bep20' ? '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' : null;
        $w->mpesa_phone = $item['method'] === 'mpesa' ? $item['phone'] : null;
        $w->mpesa_name = $item['method'] === 'mpesa' ? 'James Ke' : null;
        $w->mpesa_type = 'phone';
        $w->amount_kes = $item['method'] === 'mpesa' ? $amtKes : null;
        $w->status = $item['status'];
        $w->created_at = $created;

        if ($item['status'] === 'approved') {
            $w->approved_at = (clone $created)->addMinutes(18);
            $w->paid_at = (clone $created)->addMinutes(25);
            $w->tx_hash = $item['method'] === 'bep20' 
                ? '0x' . bin2hex(random_bytes(32)) 
                : 'RK' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } elseif ($item['status'] === 'rejected') {
            $w->approved_at = (clone $created)->addMinutes(30);
            $w->admin_notes = 'Invalid phone number formatting.';
        }

        $w->save();
    }
    echo "✓ Seeded 13 withdrawals for JamesKe (michaelkairithia3@gmail.com) spanning March 2026 to October 2026!\n";
}

echo "All tasks completed successfully!\n";
