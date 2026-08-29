<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Market;

$markets = [
    ["symbol" => "BTC/USDT", "base" => "BTC", "quote" => "USDT", "type" => "crypto"],
    ["symbol" => "ETH/USDT", "base" => "ETH", "quote" => "USDT", "type" => "crypto"],
    ["symbol" => "BNB/USDT", "base" => "BNB", "quote" => "USDT", "type" => "crypto"],
    ["symbol" => "SOL/USDT", "base" => "SOL", "quote" => "USDT", "type" => "crypto"],
    ["symbol" => "XRP/USDT", "base" => "XRP", "quote" => "USDT", "type" => "crypto"],
    ["symbol" => "DOGE/USDT", "base" => "DOGE", "quote" => "USDT", "type" => "crypto"],
    ["symbol" => "GOLD", "base" => "GOLD", "quote" => "USD", "type" => "commodities"],
    ["symbol" => "USOIL", "base" => "USOIL", "quote" => "USD", "type" => "commodities"]
];

foreach ($markets as $m) {
    Market::firstOrCreate(
        ["symbol" => $m["symbol"]],
        [
            "base_currency" => $m["base"],
            "quote_currency" => $m["quote"],
            "type" => $m["type"],
            "status" => "active",
            "last_price" => 10.0,
        ]
    );
}
echo "Done";

