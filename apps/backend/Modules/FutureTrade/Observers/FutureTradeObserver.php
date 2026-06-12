<?php

namespace Modules\FutureTrade\Observers;

use Modules\FutureTrade\DataObject\Calculate24HourPriceDto;
use Modules\FutureTrade\Entities\FuturePrice;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Events\MarketOverviewBroadcastEvent;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairDetailRepository;
use Modules\FutureTrade\Services\TradeServices\TradeService;

class FutureTradeObserver
{
    /**
     * Handle the FutureTrade "created" event.
     *
     * @param  FutureTrade  $futureTrade
     * @return void
     */
    public function created(FutureTrade $futureTrade)
    {
        $this->updateFuturePrice($futureTrade);
    }

    /**
     * Handle the FutureTrade "updated" event.
     *
     * @param  FutureTrade  $futureTrade
     * @return void
     */
    public function updated(FutureTrade $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureTrade "deleted" event.
     *
     * @param  FutureTrade  $futureTrade
     * @return void
     */
    public function deleted(FutureTrade $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureTrade "restored" event.
     *
     * @param  FutureTrade  $futureTrade
     * @return void
     */
    public function restored(FutureTrade $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureTrade "force deleted" event.
     *
     * @param  FutureTrade  $futureTrade
     * @return void
     */
    public function forceDeleted(FutureTrade $futureTrade)
    {
        //
    }

    /**
     * Update FuturePrice when a trade is created.
     * Updates market price, 24h changes, highs, lows, and volumes.
     *
     * @param FutureTrade $futureTrade
     * @return void
     */
    private function updateFuturePrice(FutureTrade $futureTrade)
    {
        $pairId = $futureTrade->future_coin_pair_id;
        $coinPair = $futureTrade->coin_pair ?? $futureTrade->load('coin_pair')->coin_pair;
        $pairDto = Calculate24HourPriceDto::fromCoinPair($coinPair);
        $price24HourData = TradeService::calculate24HourData($pairDto, $futureTrade);
        $volume24HourUsdt = (string) FutureTrade::where('future_coin_pair_id', $pairId)
            ->where('created_at', '>=', now()->subDay())
            ->sum('total_price');

        $markPrice = app('future-cache')->getMarkPrice($coinPair->id) ?? 0;
        $markPrice = trim_num((string) $markPrice, $coinPair->trade_decimal ?: 8) ?: '0';

        $indexPrice = app('future-cache')->getIndexPrice($coinPair->id) ?? 0;
        $indexPrice = trim_num((string) $indexPrice, $coinPair->trade_decimal ?: 8) ?: '0';

        $priceData = FuturePrice::updateOrCreate(
            ['future_coin_pair_id' => $pairId],
            [
                'future_coin_pair_uid' => $coinPair->uid,
                'market_price' => $futureTrade->price,
                'mark_price' => $markPrice,
                'index_price' => $indexPrice,
                'change_24h' => (string) ($price24HourData['change'] ?? 0),
                'high_24h' => (string) ($price24HourData['high'] ?? 0),
                'low_24h' => (string) ($price24HourData['low'] ?? 0),
                'volume_24h_btc' => (string) ($price24HourData['volume'] ?? 0),
                'volume_24h_usdt' => $volume24HourUsdt,
            ]
        );

        $coinPairDetails = app(CoinPairDetailRepository::class)->getCoinPairWithDetails($coinPair->code);

        $marketData = array_merge($coinPairDetails, [
            'previous_price' => $futureTrade->last_price,
            'price' => $priceData->market_price,
            'mark_price' => $priceData->mark_price,
            'index_price' => $priceData->index_price,
            'change' => $priceData->change_24h,
            'high' => $priceData->high_24h,
            'low' => $priceData->low_24h,
            'base_volume' => $priceData->volume_24h_btc,
            'volume' => $priceData->volume_24h_usdt,
            'coin_type' => $coinPair->base_coin_code,
            'base_coin_type' => $coinPair->trade_coin_code,
        ]);

        rescue(fn() => MarketOverviewBroadcastEvent::dispatch($marketData), report: false);
    }
}
