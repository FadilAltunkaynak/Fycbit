<?php
namespace Modules\FutureTrade\Services\CoinPairService\Traits;

use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;

trait CoinPairTradeCheck
{
    public function hasOpenBuyOrder(FutureCoinPair $coin_pair): bool
    {
        $openOrders = app(IOrderRepository::class)->getOrderBuilder(
            coinPair: $coin_pair->id,
            orderType: OrderType::BUY
        )->exists();
        return $openOrders;
    }

    public function hasOpenSellOrder(FutureCoinPair $coin_pair): bool
    {
        $openOrders = app(IOrderRepository::class)->getOrderBuilder(
            coinPair: $coin_pair->id,
            orderType: OrderType::SELL
        )->exists();
        return $openOrders;
    }

    public function hasOpenAnyOrder(FutureCoinPair $coin_pair): bool
    {
        $hasOpenOrder = $this->hasOpenBuyOrder($coin_pair) || $this->hasOpenSellOrder($coin_pair);
        return $hasOpenOrder;
    }

    public function hasOpenBuyPosition(): bool
    {
        return false;
    }

    public function hasOpenSellPosition(): bool
    {
        return false;
    }

    public function hasOpenAnyPosition(): bool
    {
        $hasOpenPosition = $this->hasOpenBuyPosition() || $this->hasOpenSellPosition();
        return $hasOpenPosition;
    }
}