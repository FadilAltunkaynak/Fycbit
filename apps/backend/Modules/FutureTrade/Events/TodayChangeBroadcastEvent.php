<?php

namespace Modules\FutureTrade\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Services\CoinPairService\CoinPairDetailService;

class TodayChangeBroadcastEvent implements ShouldBroadcastNow
{
    use SerializesModels, Dispatchable;

    private $channel = 'future_trade';
    private $event = 'future.trade.24h.change.';
    private ?FutureCoinPair $coinPair;
    public array $broadcastData = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        private int $id,
    ) {
        $this->coinPair = FutureCoinPair::find($this->id);
        if($this->coinPair) {
            $this->event .= $this->coinPair->uid;

            $service = app(CoinPairDetailService::class);
            $response = $service->getCoinPairDetailsByCoinCode($this->coinPair->code);

            $data = $response['data'] ?? [];
            $this->broadcastData = $data;
        }
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel($this->channel);
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