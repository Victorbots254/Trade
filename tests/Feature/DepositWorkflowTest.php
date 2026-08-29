<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_deposit_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/deposits', [
            'currency' => 'USDT',
            'amount' => 500.00,
            'tx_hash' => '0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('deposits', [
            'user_id' => $user->id,
            'currency' => 'USDT',
            'amount' => 500.00,
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_txhash_submission_is_blocked(): void
    {
        $user = User::factory()->create();
        Deposit::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'amount' => 100.00,
            'tx_hash' => '0xDUPLICATE_TX_HASH',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->postJson('/api/deposits', [
            'currency' => 'USDT',
            'amount' => 100.00,
            'tx_hash' => '0xDUPLICATE_TX_HASH',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_approve_deposit_and_credit_wallet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'amount' => 1000.00,
            'tx_hash' => '0xVALID_TX_HASH_APPROVE',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/admin/deposits/{$deposit->id}/approve");

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $wallet = Wallet::where('user_id', $user->id)->where('currency', 'USDT')->first();
        $this->assertNotNull($wallet);
        $this->assertEquals(1000.00, (float)$wallet->available_balance);
    }
}
