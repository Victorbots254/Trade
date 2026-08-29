<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Market;

echo "Updating DB prices from Binance...\n";
$json = file_get_contents("https://api.binance.com/api/v3/ticker/24hr");
$data = json_decode($json, true);

$map = [];
foreach ($data as $t) {
    $map[$t["symbol"]] = $t;
}

$markets = Market::all();
foreach ($markets as $m) {
    $clean = str_replace("/", "", $m->symbol);
    if (isset($map[$clean])) {
        $m->last_price = (float) $map[$clean]["lastPrice"];
        $m->save();
    }
}
echo "Prices updated!\n";

