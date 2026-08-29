<?php

namespace App\Events;

use App\Models\Deposit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewDepositPendingApproval implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Deposit $deposit) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.deposits'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->deposit->id,
            'user_id' => $this->deposit->user_id,
            'user_email' => $this->deposit->user->email ?? 'N/A',
            'amount' => (float) $this->deposit->amount,
            'currency' => $this->deposit->currency,
            'tx_hash' => $this->deposit->tx_hash,
            'bscscan_url' => "https://bscscan.com/tx/{$this->deposit->tx_hash}",
            'created_at' => $this->deposit->created_at->toDateTimeString(),
        ];
    }
}
