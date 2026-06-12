<?php

namespace Modules\FutureTrade\Services\CostService;

use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Services\CostService\OrderCost;
use Modules\FutureTrade\Services\CostService\PositionCost;
use Modules\FutureTrade\Services\MathService\LeverageMath;
use Modules\FutureTrade\Services\OrderService\OrderService;

class CostService
{
    private OrderCost $orderCost;
    private PositionCost $positionCost;

    public ?array $ignore_ids = null;
    public array $closeable_position_reduction_status = [];
    public array $closeable_position_remaining_margin = [];

    public function __construct(private int $user_id)
    {
        $this->orderCost = new OrderCost($user_id);
        $this->positionCost = new PositionCost($user_id);
    }

    public function getCostByNewOrder(FutureOrderData $orderData): string|float
    {
        $decimal = $orderData->coinPair->trade_decimal ?: 8;

        $user_cost = $this->getUserCost();
        $order_cost = $this->calculateOrderCostWithCloseableReduction($orderData);

        return bcaddx($user_cost, $order_cost, $decimal);
    }

    public function getUserCost(): string|float
    {
        $decimal = 8;

        $positionsByPair = $this->getOpenPositionsByPair();
        $this->recalculateOpenOrderCostsWithCloseablePositions($positionsByPair);
        $this->calculateCloseablePositionReductionStatus($positionsByPair);
        $this->positionCost->cost($this->ignore_ids);

        $total_order_cost = bcaddx($this->orderCost->sell_order_cost, $this->orderCost->buy_order_cost, $decimal);

        $total_position_cost = $this->positionCost->total_margin;

        if (bccompx($this->positionCost->total_pnl, "0") == -1) {
            $total_position_cost = bcaddx($this->positionCost->total_margin, abs($this->positionCost->total_pnl), $decimal);
        }

        if (bccompx($this->positionCost->total_pnl, "0") == 1) {
            $total_position_cost = bcsubx($this->positionCost->total_margin, $this->positionCost->total_pnl, $decimal);
        }

        return bcaddx($total_order_cost, $total_position_cost, $decimal);
    }

    private function recalculateOpenOrderCostsWithCloseablePositions(?array $positionsByPair = null): void
    {
        $positionsByPair ??= $this->getOpenPositionsByPair();
        $this->closeable_position_remaining_margin = [];

        $this->orderCost->buy_order_cost = $this->calculatePendingOrderCostWithReduction(
            modelClass: FutureBuy::class,
            closeableOppositeSide: OrderType::SELL,
            positionsByPair: $positionsByPair
        );

        $this->orderCost->sell_order_cost = $this->calculatePendingOrderCostWithReduction(
            modelClass: FutureSell::class,
            closeableOppositeSide: OrderType::BUY,
            positionsByPair: $positionsByPair
        );

        $this->orderCost->total_cost = bcaddx($this->orderCost->buy_order_cost, $this->orderCost->sell_order_cost, 8);
    }

    private function calculateCloseablePositionReductionStatus(array $positionsByPair): void
    {
        $this->closeable_position_reduction_status = [];

        foreach ($positionsByPair as $pairId => $positions) {
            $positionsBySide = [
                OrderType::BUY->value => [],
                OrderType::SELL->value => [],
            ];

            foreach ($positions as $position) {
                $positionSide = $position->order_type instanceof OrderType
                    ? $position->order_type->value
                    : $position->order_type;
                $positionsBySide[$positionSide][] = $position;
            }

            foreach ($positionsBySide as $side => $sidePositions) {
                if (! $sidePositions) {
                    continue;
                }

                $total_position_amount = '0';
                $total_position_margin = '0';
                $decimal = $sidePositions[0]->coinPair->trade_decimal ?: 8;

                foreach ($sidePositions as $position) {
                    $amount = abs($position->amount);
                    $total_position_amount = bcaddx($total_position_amount, $amount, $decimal);
                    $total_position_margin = bcaddx(
                        $total_position_margin,
                        LeverageMath::initial_margin(
                            price: $position->price,
                            amount: $amount,
                            leverage: $position->leverage,
                            scale: $decimal
                        ),
                        $decimal
                    );
                }

                $reduced_amount = $this->closeable_position_remaining_margin[$pairId][$side] ?? 0;

                $status = 0;
                if (bccompx((string) $reduced_amount, '0') === 1) {
                    if (bccompx((string) $reduced_amount, (string) $total_position_amount) >= 0) {
                        $status = 1;
                    } else {
                        $status = 2;
                    }
                }

                $this->closeable_position_reduction_status[$pairId][$side] = $status;
            }
        }
    }

    private function calculateMarginForAmount(array $positions, string|float $amount): string|float
    {
        $remaining = (string) $amount;
        $total = '0';

        foreach ($positions as $position) {
            $decimal = $position->coinPair->trade_decimal ?: 8;
            if (bccompx($remaining, '0') !== 1) {
                break;
            }

            $position_amount = (string) abs($position->amount);
            $use_amount = bccompx($remaining, $position_amount) === 1 ? $position_amount : $remaining;

            $total = bcaddx(
                $total,
                LeverageMath::initial_margin(
                    price: $position->price,
                    amount: $use_amount,
                    leverage: $position->leverage,
                    scale: $decimal
                ),
                $decimal
            );

            $remaining = bcsubx($remaining, $use_amount, $decimal);
        }

        return $total;
    }

    private function calculateOrderCostWithCloseableReduction(FutureOrderData $orderData): string|float
    {
        $decimal = $orderData->coinPair->trade_decimal ?: 8;
        $leverage = property_exists($orderData, 'leverage') ? $orderData->leverage : $orderData->userLeverage->leverage;

        $initial_margin = LeverageMath::initial_margin(
            price: $orderData->price,
            amount: $orderData->amount,
            leverage: $leverage,
            scale: $decimal
        );

        [, $taker_fees] = OrderService::getTakerAndMakerFees(
            coinPair: $orderData->coinPair,
            price: $orderData->price,
            processed_amount: $orderData->amount
        );

        $order_cost = bcaddx($initial_margin, $taker_fees, $decimal);

        $opposite_position_side = $orderData->order_type === OrderType::BUY
            ? OrderType::SELL->value
            : OrderType::BUY->value;

        $pair_id = $orderData->coinPair->id;
        $status = $this->closeable_position_reduction_status[$pair_id][$opposite_position_side] ?? 0;
        $reduced_amount = $this->closeable_position_remaining_margin[$pair_id][$opposite_position_side] ?? 0;
        $positions = $this->getOpenPositionsByPair()[$pair_id] ?? [];

        if ($status == 1 || ! $positions) {
            return $order_cost;
        }

        $opposite_positions = array_values(array_filter(
            $positions,
            fn($position) => ($position->order_type instanceof OrderType ? $position->order_type->value : $position->order_type) === $opposite_position_side
        ));

        if (! $opposite_positions) {
            return $order_cost;
        }

        $total_position_amount = '0';
        foreach ($opposite_positions as $position) {
            $total_position_amount = bcaddx($total_position_amount, abs($position->amount), $decimal);
        }

        $remaining_amount = bcsubx($total_position_amount, (string) $reduced_amount, $decimal);

        if (bccompx($remaining_amount, '0') != 1) {
            return $order_cost;
        }

        if (bccompx((string) $orderData->amount, '0') != 1) {
            return $order_cost;
        }

        if (bccompx($remaining_amount, (string) $orderData->amount) >= 0) {
            return $taker_fees;
        }

        $reduction_ratio = bcdivx($remaining_amount, (string) $orderData->amount, $decimal);
        $reducible_margin = bcmulx($initial_margin, $reduction_ratio, $decimal);

        $adjusted_margin = bcsubx(
            $initial_margin > $reducible_margin ? $initial_margin : $reducible_margin,
            $initial_margin > $reducible_margin ? $reducible_margin : $initial_margin,
            $decimal
        );

        return bcaddx($adjusted_margin, $taker_fees, $decimal);
    }

    private function calculatePendingOrderCostWithReduction(
        string $modelClass,
        OrderType $closeableOppositeSide,
        array $positionsByPair
    ): string|float {
        $user_id = $this->user_id;

        $orders = $modelClass::with([
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

        $total_cost = 0;
        $reduced_amount_by_pair = [];
        $closeableOppositeValue = $closeableOppositeSide->value;

        foreach ($orders as $order) {
            $order->amount = $order->pending_amount;
            $decimal = $order->coinPair->trade_decimal ?: 8;

            [$maker_fees] = OrderService::getTakerAndMakerFees(
                coinPair: $order->coinPair,
                price: $order->price,
                processed_amount: $order->amount
            );

            $positions = $positionsByPair[$order->future_coin_pair_id] ?? null;
            $pair_id = $order->future_coin_pair_id;
            $reduced_amount_by_pair[$pair_id] ??= '0';

            if ($positions && bccompx($reduced_amount_by_pair[$pair_id], abs($order->amount)) === -1) {
                $reduce_cost = 0;
                foreach ($positions as $position) {
                    $positionSide = $position->order_type instanceof OrderType
                        ? $position->order_type->value
                        : $position->order_type;

                    if ($positionSide !== $closeableOppositeValue) {
                        continue;
                    }

                    if(bccompx($reduced_amount_by_pair[$pair_id], abs($position->amount)) >= 0){
                        continue;
                    }

                    $requested_amount = abs(bcsubx(abs($order->amount), $reduced_amount_by_pair[$pair_id], $decimal));
                    $amount = bcsubx(abs($position->amount), $requested_amount, $decimal);

                    if($amount <= 0){
                        $reduced_amount_by_pair[$pair_id] = abs($position->amount);
                    }

                    if($amount > 0){
                        $reduced_amount_by_pair[$pair_id] = bcaddx($reduced_amount_by_pair[$pair_id],  $amount, $decimal);
                    }

                    $reduce_cost = bcaddx(
                        $reduce_cost,
                        LeverageMath::initial_margin(
                            price: $position->price,
                            amount: abs($amount),
                            leverage: $position->leverage,
                            scale: $decimal
                        ),
                        $decimal
                    );
                }

                // $initial_margin = $reduce_cost > 0 ?: $initial_margin;
                $cost = bcaddx($reduce_cost, $maker_fees, $decimal);
                $total_cost = bcaddx($total_cost, $cost, $decimal);
            }else{
                $leverage = property_exists($order, 'leverage') ? $order->leverage : $order->userLeverage->leverage;
                $initial_margin = LeverageMath::initial_margin(
                    price: $order->price,
                    amount: $order->amount,
                    leverage: $leverage,
                    scale: $decimal
                );
                $cost = bcaddx($initial_margin, $maker_fees, $decimal);
                $total_cost = bcaddx($total_cost, $cost, $decimal);
            }
        }

        foreach ($reduced_amount_by_pair as $pair_id => $amount) {
            $side = $closeableOppositeSide->value;
            $this->closeable_position_remaining_margin[$pair_id][$side] = $amount;
        }

        return $total_cost;
    }

    private function getOpenPositionsByPair(): array
    {
        $positions = FuturePosition::query()
            ->where('user_id', $this->user_id)
            ->where('amount', '<>', 0)
            ->where('status', PositionStatusEnum::OPEN->value)
            // ->where('margin_mode', MarginModeEnum::CROSS->value)
            ->with('coinPair:id,trade_decimal')
            ->when($this->ignore_ids, fn($query) => $query->whereNotIn('id', $this->ignore_ids))
            ->get();

        $positionsByPair = [];

        foreach ($positions as $position) {
            $positionsByPair[$position->future_coin_pair_id][] = $position;
        }

        return $positionsByPair;
    }
}
