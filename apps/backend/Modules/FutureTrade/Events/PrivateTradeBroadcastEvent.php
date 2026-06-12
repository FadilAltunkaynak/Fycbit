<?php

namespace Modules\FutureTrade\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateTradeBroadcastEvent implements ShouldBroadcastNow
{
    use SerializesModels, Dispatchable;

    private $channel = 'future_trade_';
    private $event = 'future.trade.';
    public array $broadcastData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public int $user_id,
        public string $coinPairUid,
        public mixed $trade
    ) {
        $this->event .= $coinPairUid;

        $this->broadcastData = [
            'created_at' => $trade->created_at,
            'price' => truncate_num($trade->price, $trade->coin_pair?->trade_decimal ?: 8),
            'previous_price' => truncate_num($trade->last_price, $trade->coin_pair?->trade_decimal ?: 8),
            'amount' => truncate_num($trade->amount, $trade->coin_pair?->trade_decimal ?: 8),
        ];
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("{$this->channel}{$this->user_id}");
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
        return $this->broadcastData;
    }

    public function shouldQueue()
    {
        return false;
    }
}