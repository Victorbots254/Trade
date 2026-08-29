<?php

namespace Tests\Feature;

use App\Models\BinaryOptionContract;
use App\Models\Market;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BinaryOptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_time_expiry_option_contract()
    {
        $user = User::factory()->create();
        $market = Market::create([
            'symbol' => 'BTC/USDT',
            'base_currency' => 'BTC',
            'quote_currency' => 'USDT',
            'last_price' => 80000.00,
            'status' => 'active',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'available_balance' => 500.00,
            'locked_balance' => 0.00,
        ]);

        $response = $this->actingAs($user)->postJson('/api/options', [
            'market_id' => $market->id,
            'direction' => 'higher',
            'amount' => 100.00,
            'duration_seconds' => 60,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('binary_option_contracts', [
            'user_id' => $user->id,
            'market_id' => $market->id,
            'direction' => 'higher',
            'investment_amount' => 100.00,
            'status' => 'active',
        ]);

        $wallet = Wallet::where('user_id', $user->id)->where('currency', 'USDT')->first();
        $this->assertEquals(400.00, $wallet->available_balance);
        $this->assertEquals(100.00, $wallet->locked_balance);
    }

    public function test_contract_settlement_credits_payout_on_win()
    {
        $user = User::factory()->create();
        $market = Market::create([
            'symbol' => 'BTC/USDT',
            'base_currency' => 'BTC',
            'quote_currency' => 'USDT',
            'last_price' => 80000.00,
            'status' => 'active',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'available_balance' => 400.00,
            'locked_balance' => 100.00,
        ]);

        $contract = BinaryOptionContract::create([
            'user_id' => $user->id,
            'market_id' => $market->id,
            'direction' => 'higher',
            'entry_price' => 80000.00,
            'investment_amount' => 100.00,
            'payout_rate' => 0.88,
            'payout_amount' => 188.00,
            'duration_seconds' => 60,
            'expires_at' => now()->subSecond(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->postJson("/api/options/{$contract->id}/settle", [
            'strike_price' => 81000.00, // Ended higher -> WIN!
        ]);

        $response->assertStatus(200);
        $contract->refresh();
        $this->assertEquals('win', $contract->status);

        $wallet = Wallet::where('user_id', $user->id)->where('currency', 'USDT')->first();
        $this->assertEquals(588.00, $wallet->available_balance); // 400 + 188
        $this->assertEquals(0.00, $wallet->locked_balance);
    }
}
