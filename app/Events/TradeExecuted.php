<?php

namespace App\Events;

use App\Models\Trade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeExecuted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Trade $trade) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('market.' . str_replace('/', '_', $this->trade->market->symbol)),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->trade->id,
            'price' => (float) $this->trade->price,
            'quantity' => (float) $this->trade->quantity,
            'side' => $this->trade->side,
            'timestamp' => $this->trade->created_at->toIso8601String(),
        ];
    }
}
