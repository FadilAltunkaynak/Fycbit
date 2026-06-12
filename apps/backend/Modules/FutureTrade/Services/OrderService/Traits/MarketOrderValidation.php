<?php

namespace Modules\FutureTrade\Services\OrderService\Traits;

use App\Facades\ResponseFacade;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureCoinPair;

trait MarketOrderValidation
{
    public function checkMarketValidation(FutureCoinPair $coin_pair, float $market_price, float $price): bool
    {
        return true;
    }

    /**
     * Summary of checkMarketSlippage
     * @param OrderType $orderType
     * @param float $slippage_percent
     * @param float $price
     * @return float|string
     */
    public function checkMarketSlippage(OrderType $orderType, float $slippage_percent, float $price): float
    {
        $percent = bcadd($slippage_percent, 100);
        $slippage= bcmul($price, $percent);

        if(!$slippage) return $price;

        return match($orderType) {
            OrderType::BUY  => (float) bcaddx($price, $slippage),
            OrderType::SELL => (float) bcsubx($price, $slippage)
        };
    }
}