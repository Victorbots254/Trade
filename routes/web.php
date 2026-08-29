<?php

use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\TermsController;
use App\Http\Controllers\BinaryOptionController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\OrderController;
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
});

// Dedicated Authenticated User Pages
Route::get('/trades', [TradingTerminalController::class, 'myTrades'])->middleware('auth')->name('trades');
Route::get('/deposit', fn () => Inertia::render('Wallet/Deposit', [
    'custodialAddress' => config('app.bep20_custodial_address', '0x71C7656EC7ab88b098defB751B7401B5f6d8976F'),
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
Route::get('/profile', fn (Request $request) => Inertia::render('Profile/Show', [
    'user' => $request->user(),
    'wallets' => Wallet::where('user_id', $request->user()->id)->get(),
    'markets' => Market::where('status', 'active')->get(),
]))->middleware('auth')->name('profile');

// Public Legal Pages
Route::get('/terms', [TradingTerminalController::class, 'terms'])->name('terms');
Route::get('/privacy', [TradingTerminalController::class, 'privacy'])->name('privacy');
Route::get('/risk-disclosure', [TradingTerminalController::class, 'riskDisclosure'])->name('risk-disclosure');

// Public Company & Info Pages
Route::get('/about', fn () => Inertia::render('About'))->name('about');
Route::get('/blog', fn () => Inertia::render('Blog'))->name('blog');
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

    return response()->json(['user' => $user, 'message' => 'Registration successful']);
});

Route::post('/api/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
        $request->session()->regenerate();
        return response()->json(['user' => Auth::user(), 'message' => 'Login successful']);
    }

    return response()->json(['message' => 'Invalid email or password credentials.'], 422);
});

Route::post('/api/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out successfully']);
});

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
});

// Admin Panel Routes
Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->group(function () {
    // Admin Deposit Approvals
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('admin.deposits');
    Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve'])->name('admin.deposits.approve');
    Route::post('/deposits/{deposit}/reject', [AdminDepositController::class, 'reject'])->name('admin.deposits.reject');

    // Admin Users & Trading Control Engine
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/users/bulk-mode', [AdminUserController::class, 'bulkUpdateOutcomeMode'])->name('admin.users.bulk_mode');
    Route::post('/users/{user}/mode', [AdminUserController::class, 'updateOutcomeMode'])->name('admin.users.mode');
});
