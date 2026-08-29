<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Market;

echo "Fetching from Binance...\n";
$json = file_get_contents("https://api.binance.com/api/v3/ticker/24hr");
$data = json_decode($json, true);

$usdtPairs = [];
foreach ($data as $t) {
    if (str_ends_with($t["symbol"], "USDT")) {
        $usdtPairs[] = $t;
    }
}

// Sort by volume descending
usort($usdtPairs, function($a, $b) {
    return $b["quoteVolume"] <=> $a["quoteVolume"];
});

// Take top 250
$top = array_slice($usdtPairs, 0, 250);

$count = 0;
foreach ($top as $t) {
    $base = substr($t["symbol"], 0, -4);
    $symbol = $base . "/USDT";
    
    Market::firstOrCreate(
        ["symbol" => $symbol],
        [
            "base_currency" => $base,
            "quote_currency" => "USDT",
            "type" => "crypto",
            "status" => "active",
            "last_price" => (float) $t["lastPrice"]
        ]
    );
    $count++;
}

echo "Added $count top USDT markets!\n";

