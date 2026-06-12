<?php

namespace Modules\FutureTrade\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FutureTrade\Emum\PositionBroadcastEventEnum;

class PositionBroadcastEvent implements ShouldBroadcastNow
{
    use SerializesModels, Dispatchable;

    private $channel = 'future_trade_';
    private $event = 'future.position';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public int $user_id,
        public mixed $broadcastData,
    ) {}

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("$this->channel$this->user_id");
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