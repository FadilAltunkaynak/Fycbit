<?php

namespace Modules\FutureTrade\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\FutureTrade\DataObject\Calculate24HourPriceDto;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Services\TradeServices\TradeService;

class FutureCoinPairApiList extends JsonResource
{
    private bool $changeDataFetched = false;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     *
     * @see FutureCoinPair
     */
    public function toArray($request): array
    {
        $changeData = [];

        $changeData = TradeService::calculate24HourData(
            pairDto: Calculate24HourPriceDto::fromParam(
                base_id: $this->base_coin_id,
                trade_id: $this->trade_coin_id,
            )
        );

        $tradeDetails = app(TradeService::class)->repository->getLastTrade(
            coin_id: $this->id,
        );

        $market_price = app('future-cache')->getMarketPrice($this->id) ?? 0;
        $market_price = truncate_num((string) $market_price ?: 0, '0') ?: '0';

        $previous_price = truncate_num((string) $tradeDetails?->last_price ?: 0, 0) ?: 0;
        $previous_price = $previous_price ?: $market_price ?: '0';

        return [
            'uid' => $this->uid,
            'code' => $this->code,
            'base_decimal' => $this->base_decimal,
            'trade_decimal' => $this->trade_decimal,
            'market_price' => $market_price,
            'previous_price' => $previous_price,
            ...$changeData
        ];
    }
}
