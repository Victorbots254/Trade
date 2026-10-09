<?php

use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminP2PController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TermsController;
use App\Http\Controllers\BinaryOptionController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\P2P\P2PMarketplaceController;
use App\Http\Controllers\P2P\P2PMerchantController;
use App\Http\Controllers\P2P\P2POrderController;
use App\Http\Controllers\TradingTerminalController;
use App\Models\Market;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Landing Page
Route::get('/', function () {
    return Inertia::render('Landing', [
        'user' => Auth::user(),
        'markets' => Market::where('status', 'active')->get(),
    ]);
})->name('home');

// P2P Marketplace Public Route
Route::get('/p2p', [P2PMarketplaceController::class, 'index'])->name('p2p.index');

// Spot Trading Terminal SPA Routes
Route::get('/terminal', [TradingTerminalController::class, 'index'])->name('terminal');
Route::get('/trade/{symbol}', [TradingTerminalController::class, 'index'])->name('terminal.symbol');

// Time-Expiry Quick Options Trading Routes (1m, 5m, 15m, 30m, 1h)
Route::get('/options', [BinaryOptionController::class, 'index'])->name('options');
Route::get('/trade/options/{symbol}', [BinaryOptionController::class, 'index'])->name('options.symbol');

// Dedicated Guest-Only Auth Pages (Redirects logged in users to /terminal)
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
    Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register');
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetPassword'])->name('password.reset');
});

// Dedicated Authenticated User Pages
Route::get('/trades', [TradingTerminalController::class, 'myTrades'])->middleware('auth')->name('trades');
Route::get('/deposit', fn () => Inertia::render('Wallet/Deposit', [
    'custodialAddress' => \App\Models\Setting::get('custodial_bep20_address', config('app.bep20_custodial_address', '0x71C7656EC7ab88b098defB751B7401B5f6d8976F')),
    'depositQrImage' => \App\Models\Setting::get('deposit_qr_image'),
]))->middleware('auth')->name('deposit');
Route::get('/payments', fn (Request $request) => Inertia::render('Wallet/Payments', [
    'user' => $request->user(),
    'wallets' => Wallet::where('user_id', $request->user()->id)->get(),
    'markets' => Market::where('status', 'active')->get(),
]))->middleware('auth')->name('payments');
Route::get('/payments/binance-guide', fn (Request $request) => Inertia::render('Wallet/BinanceGuide', [
    'user' => $request->user(),
    'wallets' => Wallet::where('user_id', $request->user()->id)->get(),
    'markets' => Market::where('status', 'active')->get(),
]))->middleware('auth')->name('payments.guide');
Route::get('/profile', function (Request $request) {
    \App\Services\WalletReconciliationService::reconcileUsdtWallet($request->user(), false);
    \App\Services\WalletReconciliationService::reconcileUsdtWallet($request->user(), true);

    $user = $request->user()->fresh();

    $mmfLocked = (float) \App\Models\MmfSubscription::where('user_id', $user->id)
        ->where('status', 'locked')
        ->sum('amount');

    $mmfActiveCount = (int) \App\Models\MmfSubscription::where('user_id', $user->id)
        ->where('status', 'locked')
        ->count();

    $mmfInterestEarned = (float) \App\Models\MmfInterestLog::where('user_id', $user->id)
        ->sum('amount');

    return Inertia::render('Profile/Show', [
        'user' => $user,
        'wallets' => Wallet::where('user_id', $user->id)->get(),
        'markets' => Market::where('status', 'active')->get(),
        'mmf_locked' => $mmfLocked,
        'mmf_active_count' => $mmfActiveCount,
        'mmf_interest_earned' => $mmfInterestEarned,
        'custodialAddress' => \App\Models\Setting::get('custodial_bep20_address', config('app.bep20_custodial_address', '0x71C7656EC7ab88b098defB751B7401B5f6d8976F')),
    ]);
})->middleware('auth')->name('profile');

Route::post('/api/profile/update', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'current_password' => 'nullable|string',
        'new_password' => 'nullable|string|min:8|confirmed',
    ]);

    $user = $request->user();

    if ($request->filled('new_password')) {
        if (!$request->filled('current_password') || !\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password does not match.'], 422);
        }
        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
    }

    $user->name = $request->name;
    $user->save();

    return response()->json([
        'message' => 'Profile updated successfully!',
        'user' => $user->fresh(),
    ]);
})->middleware('auth');

Route::post('/api/wallet/reconcile-funds', function (Request $request) {
    $wallet = \App\Services\WalletReconciliationService::reconcileUsdtWallet($request->user(), false);
    return response()->json([
        'message' => 'Funds reconciled! Available balance: $' . number_format((float)$wallet->available_balance, 2) . ' USDT.',
        'available_balance' => (float)$wallet->available_balance,
        'locked_balance' => (float)$wallet->locked_balance,
    ]);
})->middleware('auth');

// Public Legal Pages
Route::get('/terms', [TradingTerminalController::class, 'terms'])->name('terms');
Route::get('/privacy', [TradingTerminalController::class, 'privacy'])->name('privacy');
Route::get('/risk-disclosure', [TradingTerminalController::class, 'riskDisclosure'])->name('risk-disclosure');

// Public Company & Info Pages
Route::get('/about', fn () => Inertia::render('About'))->name('about');
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog');
Route::get('/careers', fn () => Inertia::render('Careers'))->name('careers');
Route::get('/bug-bounty', fn () => Inertia::render('BugBounty'))->name('bug-bounty');
Route::get('/media-kit', fn () => Inertia::render('MediaKit'))->name('media-kit');
Route::get('/faq', fn () => Inertia::render('FAQ'))->name('faq');
Route::get('/contact', fn () => Inertia::render('Contact'))->name('contact');


// Simple Auth Endpoints for SPA
Route::post('/api/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'accepted_terms' => 'accepted',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'accepted_terms_at' => now(),
        'accepted_terms_ip' => $request->ip(),
    ]);

    // Create initial $0.00 USDT LIVE balance on registration
    \App\Models\Wallet::create([
        'user_id' => $user->id,
        'currency' => 'USDT',
        'is_demo' => false,
        'available_balance' => 0.00,
        'locked_balance' => 0.00,
    ]);

    // Create initial $10,000.00 USDT DEMO balance on registration
    \App\Models\Wallet::create([
        'user_id' => $user->id,
        'currency' => 'USDT',
        'is_demo' => true,
        'available_balance' => 10000.00,
        'locked_balance' => 0.00,
    ]);

    Auth::login($user);

    // Send Clean Trading-Style Welcome Email
    try {
        $verifyUrl = \Illuminate\Support\Facades\URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);
        \App\Services\TradingEmailService::sendWelcomeEmail($user, $verifyUrl);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Welcome email dispatch error: ' . $e->getMessage());
    }

    return response()->json(['user' => $user, 'message' => 'Registration successful! Welcome email sent.']);
});

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid or expired verification link.');
    }
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
    return redirect()->route('profile')->with('success', 'Your email address has been verified successfully!');
})->name('verification.verify');

Route::post('/api/email/verify/resend', function (Request $request) {
    $user = $request->user();
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Your email is already verified.']);
    }
    $verifyUrl = \Illuminate\Support\Facades\URL::signedRoute('verification.verify', [
        'id' => $user->id,
        'hash' => sha1($user->getEmailForVerification()),
    ]);
    \App\Services\TradingEmailService::sendVerificationEmail($user, $verifyUrl);
    return response()->json(['message' => 'Verification email sent. Please check your inbox.']);
})->middleware('auth');

Route::post('/api/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
        $request->session()->regenerate();
        $user = Auth::user();
        $redirectUrl = $user->is_admin ? '/admin/p2p' : '/terminal';
        return response()->json([
            'user' => $user,
            'redirect' => $redirectUrl,
            'message' => 'Login successful',
        ]);
    }

    return response()->json(['message' => 'Invalid email or password credentials.'], 422);
});

Route::post('/api/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out successfully']);
});

// Password Reset API Endpoints
Route::post('/api/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/api/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

// Authenticated User Endpoints
Route::middleware('auth')->group(function () {
    Route::post('/api/terms/accept', [TermsController::class, 'accept']);

    // Demo Account Balance Reset ($10,000 USDT)
    Route::post('/api/demo/reset', function (Request $request) {
        $user = $request->user();
        $user->update(['demo_balance' => 10000.00]);

        // Reset USDT demo wallet
        \App\Models\Wallet::updateOrCreate(
            ['user_id' => $user->id, 'currency' => 'USDT', 'is_demo' => true],
            ['available_balance' => 10000.00, 'locked_balance' => 0.00]
        );

        // Delete other demo coin wallets to fully reset demo portfolio
        \App\Models\Wallet::where('user_id', $user->id)
            ->where('is_demo', true)
            ->where('currency', '!=', 'USDT')
            ->delete();

        // Cancel open demo orders
        \App\Models\Order::where('user_id', $user->id)
            ->where('is_demo', true)
            ->whereIn('status', ['open', 'partially_filled'])
            ->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Demo account successfully refilled to $10,000.00 USDT practice funds.']);
    });

    // Deposits & Payments
    Route::get('/api/deposits', [DepositController::class, 'index']);
    Route::post('/api/deposits', [DepositController::class, 'store']);

    // MegaPay M-Pesa Payments
    Route::get('/api/mpesa/settings', [\App\Http\Controllers\MegaPayController::class, 'getSettings']);
    Route::post('/api/mpesa/initiate', [\App\Http\Controllers\MegaPayController::class, 'initiate']);
    Route::get('/api/mpesa/status/{deposit}', [\App\Http\Controllers\MegaPayController::class, 'checkStatus']);
    
    // Withdrawals
    Route::get('/withdraw', [\App\Http\Controllers\WithdrawalController::class, 'index'])->name('withdraw');
    Route::post('/withdraw', [\App\Http\Controllers\WithdrawalController::class, 'store']);

    // Learning / Blog Guidelines
    Route::get('/learn', [\App\Http\Controllers\BlogController::class, 'index'])->name('learn.index');
    Route::get('/learn/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('learn.show');

    Route::post('/api/payments/bep20', function (Request $request) {
        $request->validate([
            'bep20_address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ], [
            'bep20_address.regex' => 'Please enter a valid Binance Smart Chain (BEP20) wallet address starting with 0x (42 characters long).',
        ]);

        $user = $request->user();
        $user->update(['bep20_address' => $request->bep20_address]);

        return response()->json([
            'message' => 'Binance BEP20 Wallet Address saved successfully!',
            'user' => $user->fresh(),
        ]);
    });

    // Authenticated User Profile & Live Outcome Mode
    Route::get('/api/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'trading_outcome_mode' => $request->user()->trading_outcome_mode,
        ]);
    });

    // Monthly Interests (MMF)
    Route::get('/monthly-interests', [\App\Http\Controllers\MmfController::class, 'index'])->name('monthly.interests');
    Route::post('/monthly-interests/lock', [\App\Http\Controllers\MmfController::class, 'lockFunds']);

    // Spot Orders
    Route::get('/api/orders', [OrderController::class, 'index']);
    Route::post('/api/orders', [OrderController::class, 'store'])->middleware('throttle:30,1');
    Route::delete('/api/orders/{order}', [OrderController::class, 'cancel']);

    // Time-Expiry Options Contracts
    Route::post('/api/options', [BinaryOptionController::class, 'store'])->middleware('throttle:30,1');
    Route::post('/api/options/{contract}/settle', [BinaryOptionController::class, 'settle']);

    // P2P Trading User & Escrow Routes
    Route::get('/p2p/orders', [P2PMarketplaceController::class, 'myOrders'])->name('p2p.orders.my');
    Route::post('/p2p/orders', [P2POrderController::class, 'store'])->name('p2p.order.store');
    Route::get('/p2p/orders/{order}', [P2POrderController::class, 'show'])->name('p2p.order.show');
    Route::post('/p2p/orders/{order}/paid', [P2POrderController::class, 'markPaid'])->name('p2p.order.paid');
    Route::post('/p2p/orders/{order}/release', [P2POrderController::class, 'release'])->name('p2p.order.release');
    Route::post('/p2p/orders/{order}/cancel', [P2POrderController::class, 'cancel'])->name('p2p.order.cancel');
    Route::post('/p2p/orders/{order}/dispute', [P2POrderController::class, 'dispute'])->name('p2p.order.dispute');
    Route::post('/p2p/orders/{order}/message', [P2POrderController::class, 'sendMessage'])->name('p2p.order.message');

    // P2P Merchant Ads Management
    Route::get('/p2p/merchant/ads', [P2PMerchantController::class, 'index'])->name('p2p.merchant.ads');
    Route::post('/p2p/merchant/ads', [P2PMerchantController::class, 'storeAd'])->name('p2p.merchant.ads.store');
    Route::post('/p2p/merchant/ads/{ad}/toggle', [P2PMerchantController::class, 'toggleAd'])->name('p2p.merchant.ads.toggle');
    Route::post('/p2p/merchant/ads/{ad}/close', [P2PMerchantController::class, 'closeAd'])->name('p2p.merchant.ads.close');
    Route::get('/api/p2p/market-price', [P2PMarketplaceController::class, 'getMarketPrice'])->name('p2p.market_price');
    Route::get('/api/p2p/notifications/poll', [P2POrderController::class, 'pollNotifications'])->name('p2p.notifications.poll');
});

// Unified Admin Routes (P2P Management, Deposits Verification, User Controls)
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/p2p', [AdminP2PController::class, 'index'])->name('admin.p2p');
    Route::post('/p2p/users/{user}/merchant', [AdminP2PController::class, 'toggleMerchant'])->name('admin.p2p.merchant');
    Route::post('/p2p/orders/{order}/resolve', [AdminP2PController::class, 'resolveDispute'])->name('admin.p2p.resolve');

    // Deposit Approvals & Settings
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('admin.deposits');
    Route::post('/deposits/settings', [AdminDepositController::class, 'updateSettings'])->name('admin.deposits.settings');
    Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve'])->name('admin.deposits.approve');
    Route::post('/deposits/{deposit}/reject', [AdminDepositController::class, 'reject'])->name('admin.deposits.reject');

    // User Roster & Trading Controls
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/users/bulk-mode', [AdminUserController::class, 'bulkUpdateOutcomeMode'])->name('admin.users.bulk_mode');
    Route::post('/users/{user}/mode', [AdminUserController::class, 'updateOutcomeMode'])->name('admin.users.mode');
});

// MegaPay M-Pesa Real-Time Webhooks (Public)
Route::post('/api/webhooks/megapay', [\App\Http\Controllers\MegaPayController::class, 'webhook'])->name('webhook.megapay.api');
Route::post('/webhooks/megapay', [\App\Http\Controllers\MegaPayController::class, 'webhook'])->name('webhook.megapay');

