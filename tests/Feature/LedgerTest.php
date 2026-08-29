<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_deposit_credit_updates_wallet_and_ledger(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'available_balance' => 100.00,
            'locked_balance' => 0,
        ]);

        LedgerService::recordDepositCredit($wallet, 250.50, 1);

        $wallet->refresh();
        $this->assertEquals(350.50, (float)$wallet->available_balance);

        $this->assertDatabaseHas('ledger_entries', [
            'reference_type' => 'Deposit',
            'reference_id' => 1,
            'amount' => 250.50000000,
        ]);
    }

    public function test_lock_funds_prevents_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'available_balance' => 50.00,
            'locked_balance' => 0,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        LedgerService::lockFundsForOrder($wallet, 100.00, 1);
    }
}
