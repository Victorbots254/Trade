<?php

namespace App\Console\Commands;

use App\Services\MarketDataSyncService;
use Illuminate\Console\Command;

class SyncMarketDataCommand extends Command
{
    protected $signature = 'market:sync';
    protected $description = 'Sync live market prices and 24h tickers from free public APIs';

    public function handle(): void
    {
        $this->info('Syncing live market values...');
        MarketDataSyncService::syncAllMarkets();
        $this->info('Live market values synced successfully!');
    }
}
