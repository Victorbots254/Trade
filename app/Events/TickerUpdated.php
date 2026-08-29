<?php

namespace App\Events;

use App\Models\Market;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TickerUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Market $market) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tickers'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'symbol' => $this->market->symbol,
            'last_price' => (float) $this->market->last_price,
            'change_24h' => (float) $this->market->change_24h,
            'high_24h' => (float) $this->market->high_24h,
            'low_24h' => (float) $this->market->low_24h,
            'volume_24h' => (float) $this->market->volume_24h,
        ];
    }
}
