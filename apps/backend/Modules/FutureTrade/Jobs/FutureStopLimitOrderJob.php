<?php

namespace Modules\FutureTrade\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Services\CoinPairService\CoinPairService;
use Modules\FutureTrade\Services\OrderService\OrderService;

class FutureStopLimitOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private int $futureTradeId
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderService $orderService)
    {
        $futureTrade = FutureTrade::find($this->futureTradeId);

        if(! $futureTrade){
            return;
        }

        $coinPair = CoinPairRepository::getCoinPairById($futureTrade->future_coin_pair_id);

        if(! $coinPair){
            return;
        }

        $lastTrade = FutureTrade::where('future_coin_pair_id', $futureTrade->future_coin_pair_id)->latest()->first();

        if($lastTrade?->price == $futureTrade->price){
            // return;
        }

        $buyOrders = FutureBuy::where('future_coin_pair_id', $futureTrade->future_coin_pair_id)
                    ->where('status', OrderStatusEnum::PENDING->value)
                    ->where('stop_price', $futureTrade->price)->get();

        $sellOrders = FutureSell::where('future_coin_pair_id', $futureTrade->future_coin_pair_id)
                    ->where('status', OrderStatusEnum::PENDING->value)
                    ->where('stop_price', $futureTrade->price)->get();

        array_map(function ($orders) use($orderService, $coinPair) {
            foreach ($orders as $order) {
                $order->coin_pair = $coinPair;
                $orderService->matchEngine($order);
            }
        }, [$buyOrders, $sellOrders]);
    }
}
