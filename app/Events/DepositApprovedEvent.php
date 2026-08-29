<?php

namespace App\Events;

use App\Models\Deposit;
use App\Models\Wallet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositApprovedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Deposit $deposit, public Wallet $wallet) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->deposit->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'deposit_id' => $this->deposit->id,
            'amount' => (float) $this->deposit->amount,
            'currency' => $this->deposit->currency,
            'tx_hash' => $this->deposit->tx_hash,
            'available_balance' => (float) $this->wallet->available_balance,
            'locked_balance' => (float) $this->wallet->locked_balance,
            'message' => "Your deposit of {$this->deposit->amount} {$this->deposit->currency} has been approved and credited to your wallet!",
        ];
    }
}
