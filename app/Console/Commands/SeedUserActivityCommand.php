<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\UserActivitySeeder;
use Illuminate\Console\Command;

class SeedUserActivityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:seed-activity 
                            {email? : The email of the specific user to seed (defaults to all users)}
                            {--clear : Clear previous test transaction history before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed realistic trade histories, options contracts, withdrawals, deposits, and wallets for testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $clear = (bool) $this->option('clear');

        $seeder = new UserActivitySeeder();
        $seeder->setCommand($this);

        if ($email && strtolower($email) !== 'all') {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("User with email [{$email}] not found.");
                return self::FAILURE;
            }

            $this->info("Seeding realistic transaction history for: {$user->name} ({$user->email})...");
            $seeder->run($user, $clear);
        } else {
            $this->info("Seeding realistic transaction history for all existing users...");
            $seeder->run(null, $clear);
        }

        $this->newLine();
        $this->info("✓ Successfully seeded realistic transactions across:");
        $this->line("  - Live & Demo Wallet Balances (USDT, BTC, ETH, SOL, GOLD)");
        $this->line("  - Spot Orders (Open limit orders & Filled orders)");
        $this->line("  - Binary Options Contracts (Wins, Losses, Payouts, Expiry timers)");
        $this->line("  - Deposits (Approved BEP20, M-Pesa & 1 Pending for admin approval)");
        $this->line("  - Withdrawals (Approved with Date Paid & TxID, Rejected, and 1 Pending)");
        $this->line("  - P2P Orders (Completed Buy & Sell trades)");
        $this->line("  - MMF 18% Mutual Fund (Active locked subscription & Earned interest logs)");

        return self::SUCCESS;
    }
}
