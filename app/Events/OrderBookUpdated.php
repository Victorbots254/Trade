<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderBookUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $symbol, public array $depth) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('market.' . str_replace('/', '_', $this->symbol)),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'symbol' => $this->symbol,
            'bids' => $this->depth['bids'] ?? [],
            'asks' => $this->depth['asks'] ?? [],
        ];
    }
}
