<?php

namespace App\Console\Commands;

use App\Services\MmfService;
use Illuminate\Console\Command;

class ProcessMmfMaturitiesCommand extends Command
{
    protected $signature = 'mmf:process-maturities {--user_id= : Specific user ID to process}';
    protected $description = 'Automatically unlock matured USDT MMF subscriptions and credit principal + 18% monthly yield to user wallets';

    public function handle(): int
    {
        $userId = $this->option('user_id') ? (int) $this->option('user_id') : null;

        $this->info('Checking and unlocking matured MMF subscriptions...');
        $unlocked = MmfService::processMaturities($userId);
        $this->info("Completed. Unlocked and paid out {$unlocked} matured subscription(s).");

        return 0;
    }
}
