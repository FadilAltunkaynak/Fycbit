<?php

namespace Modules\FutureTrade\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;

class OrderBookBroadcastEvent implements ShouldBroadcastNow
{
    use SerializesModels, Dispatchable;

    private $channel = 'future_trade';
    private $event = 'future.orderbook.';
    public array $broadcastData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public mixed $coin_pair_uid,
        public mixed $order
    ) {
        $this->event .= $coin_pair_uid;

        $orderType = $order->order_type;
        $orderTypeValue = $orderType instanceof OrderType ? $orderType : OrderType::tryFrom($orderType);

        if(! $orderTypeValue){
            return;
        }

        $orderBook = [];
        // $coinPair = CoinPairRepository::getCoinPairByUid($coin_pair_uid);
        $coinPair =$order->coin_pair;

        if(!$coinPair){
            return;
        }

        $repo = app(IOrderRepository::class);
        if($order->order_type == OrderType::BUY){
            $orderBook = $repo->getOrders(
                coinPair: $coinPair->id,
                orderType: OrderType::BUY,
                orderMethod: OrderMethod::LIMIT,
                paginate: false
            )->select('price', DB::raw('SUM(pending_amount) as pending_amount'))
                ->where('price', $order->price)
                ->groupBy('price')
                ->first();
        }

        if($order->order_type == OrderType::SELL){
            $orderBook = $repo->getOrders(
                coinPair: $coinPair->id,
                orderType: OrderType::SELL,
                orderMethod: OrderMethod::LIMIT,
                paginate: false
            )->select('price', DB::raw('SUM(pending_amount) as pending_amount'))
                ->where('price', $order->price)
                ->groupBy('price')
                ->first();
        }

        $this->broadcastData = [
            'order_type' => $orderTypeValue,
            'order' => $orderBook ?: ['price' => $order->price, 'pending_amount' => '0']
        ];
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
        return $this->broadcastData;
    }

    public function shouldQueue()
    {
        return false;
    }
}
