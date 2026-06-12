<?php

namespace Modules\FutureTrade\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeBroadcastEvent implements ShouldBroadcastNow
{
    use SerializesModels, Dispatchable;

    private $channel = 'future_trade';
    private $event = 'future.trade.';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        private string $coinPairUid,
        private string $coinPairCode,
        public mixed $broadcastData
    ) {
        $this->event .= $coinPairUid;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel("$this->channel");
    }

    /**
    * The event's broadcast name.
    *
    * @return string
    */
    public function broadcastAs()
    {
        return $this->event;
    }

    public function broadcastWith()
    {
        return [
            'code' => $this->coinPairCode,
            'created_at' => $this->broadcastData->created_at,
            'price' => truncate_num($this->broadcastData->price, $this->broadcastData->coin_pair?->trade_decimal ?: 8),
            'previous_price' => truncate_num($this->broadcastData->last_price, $this->broadcastData->coin_pair?->trade_decimal ?: 8),
            'amount' => truncate_num($this->broadcastData->amount, $this->broadcastData->coin_pair?->trade_decimal ?: 8),
        ];
    }

    public function shouldQueue()
    {
        return false;
    }
}