<?php

namespace App\Console\Commands;

use Database\Seeders\P2PLiquiditySeeder;
use Illuminate\Console\Command;

class SeedP2PLiquidityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:seed-liquidity {--clear : Clear existing dummy P2P ads and users before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed 100 realistic dummy P2P merchants with diverse price points and limited trade protections';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $clear = (bool) $this->option('clear');

        $this->info('Starting P2P liquidity seeder (100 merchants across price tiers)...');

        $seeder = new P2PLiquiditySeeder();
        $seeder->setCommand($this);
        $seeder->run($clear);

        $this->newLine();
        $this->info('✓ P2P Liquidity successfully populated:');
        $this->line('  - 100 dummy merchants dealing with diverse prices (KES 129.50 - 134.80 & USD/USDT)');
        $this->line('  - Real users prioritized and shown at the very top');
        $this->line('  - Dummy ads flagged with "Limited" trade status to prevent order placement');

        return self::SUCCESS;
    }
}
