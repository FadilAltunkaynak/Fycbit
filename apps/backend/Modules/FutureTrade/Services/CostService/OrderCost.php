<?php

namespace Modules\FutureTrade\Services\CostService;

use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Services\MathService\LeverageMath;
use Modules\FutureTrade\Services\OrderService\OrderService;

class OrderCost
{

    public string|float $order_cost = 0;
    public string|float $total_cost = 0;
    public string|float $buy_order_cost = 0;
    public string|float $sell_order_cost = 0;

    public function __construct(
        private int $user_id,
    ) {
    }

    private function all_buy_cost()
    {
        $user_id = $this->user_id;
        $orders = FutureBuy::with([
            'userLeverage' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            },
            'coinPair:id,trade_decimal,taker_fees_percent,maker_fees_percent'
        ])
            ->whereHas('coinPair')
            ->whereHas('userLeverage', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->where('user_id', $user_id)
            ->pendingOrder()
            ->get();

        foreach ($orders as $order) {
            $order->amount = $order->pending_amount;
            // $orderData = FutureOrderData::fromOrder(
            //     order: $order,
            //     coin_pair: $coin_pair
            // );

            $cost = $this->getOrderCost($order);
            $this->buy_order_cost = bcaddx($this->buy_order_cost, $cost, $order->coinPair->trade_decimal);
            $this->total_cost = bcaddx($this->buy_order_cost, $this->total_cost, $order->coinPair->trade_decimal);
        }

        return $this;
    }

    private function all_sell_cost()
    {
        $user_id = $this->user_id;
        $orders = FutureSell::with([
            'userLeverage' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            },
            'coinPair:id,trade_decimal,taker_fees_percent,maker_fees_percent'
        ])
            ->whereHas('coinPair')
            ->whereHas('userLeverage', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->where('user_id', $user_id)
            ->pendingOrder()
            ->get();

        foreach ($orders as $order) {
            $order->amount = $order->pending_amount;
            $cost = $this->getOrderCost($order);
            $this->sell_order_cost = bcaddx($this->sell_order_cost, $cost, $order->coinPair->trade_decimal);
            $this->total_cost = bcaddx($this->sell_order_cost, $this->total_cost, $order->coinPair->trade_decimal);
        }

        return $this;
    }

    public function getOrderCost(FutureBuy|FutureSell|FutureOrderData $orderData): string|float
    {
        $decimal = $orderData->coinPair->trade_decimal;
        $leverage = property_exists($orderData, 'leverage') ? $orderData->leverage : $orderData->userLeverage->leverage;

        $initial_margin = LeverageMath::initial_margin(
            price: $orderData->price,
            amount: $orderData->amount,
            leverage: $leverage,
            scale: $decimal
        );

        [$maker_fees, $taker_fees] = OrderService::getTakerAndMakerFees(
            coinPair: $orderData->coinPair,
            price: $orderData->price,
            processed_amount: $orderData->amount
        );

        $order_cost = bcaddx(
            $initial_margin,
            $orderData instanceof FutureOrderData ? $taker_fees : $maker_fees
        );

        return $order_cost;
    }

    public function cost()
    {
        $this->all_buy_cost();
        $this->all_sell_cost();

        return $this;
    }

    public function requestedOrderCost(FutureOrderData $orderData): static
    {
        $decimal = $orderData->coinPair->trade_decimal ?: 8;
        $cost = $this->getOrderCost($orderData);

        // START
        // Add Order Cost On Pending Order
        if ($orderData->order_type == OrderType::BUY) {
            $this->buy_order_cost = bcaddx($this->buy_order_cost, $cost, $decimal);
        } else {
            $this->sell_order_cost = bcaddx($this->sell_order_cost, $cost, $decimal);
        }
        // Add Order Cost On Pending Order
        // STOP

        $this->total_cost = bcaddx($this->total_cost, $cost, $decimal);

        $this->order_cost = $cost;

        return $this;
    }
}
