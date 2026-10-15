<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\P2PAd;
use Database\Seeders\P2PLiquiditySeeder;

echo "=== Updating P2P Usernames to Authentic Multicultural Profiles ===\n";

// Execute P2PLiquiditySeeder with clearDummy = true
$seeder = new P2PLiquiditySeeder();
$seeder->run(true);

echo "✓ P2PLiquiditySeeder executed.\n";

// Verify Top Traders
$topTraders = P2PAd::with('user')->where('is_top_trader', true)->get();
echo "\nTop Traders in P2P Ads:\n";
foreach ($topTraders as $ad) {
    echo "  - [{$ad->type}] {$ad->user?->username} (Orders: {$ad->user?->p2p_completed_trades}, Rating: {$ad->user?->p2p_rating}/5, Completion: {$ad->user?->p2p_completion_rate}%)\n";
}

// Sample of other diverse merchants
$sampleMerchants = User::where('is_dummy', true)->where('is_p2p_merchant', true)->take(15)->get();
echo "\nSample Diverse Multicultural Merchants:\n";
foreach ($sampleMerchants as $m) {
    echo "  - Username: {$m->username} | Name: {$m->name} | Orders: {$m->p2p_completed_trades} | Rating: {$m->p2p_rating}\n";
}

$totalActiveAds = P2PAd::where('status', 'active')->count();
echo "\nTotal Active P2P Ads in Marketplace: {$totalActiveAds}\n";
echo "=== Done ===\n";
