<?php

namespace Modules\FutureTrade\Services\CostService;

use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Repositories\PositionRepository\PositionRepository;
use Modules\FutureTrade\Services\MathService\LeverageMath;
use Modules\FutureTrade\Services\MathService\PositionMath;

class PositionCost
{

    public string|float $total_margin = 0;
    public string|float $total_pnl = 0;
    public string|float $total_close_fees = 0;
    public string|float $buy_reduce_cost = 0;
    public string|float $sell_reduce_cost = 0;

    public function __construct(
        private int $user_id,
    ) {}

    public function cost(?array $ignore_ids = null)
    {
        $this->all_position_cost($ignore_ids);
        return $this;
    }

    public function reduce_position_cost(FutureOrderData $orderData)
    {
        /** @var FuturePosition $position */
        $openPosition = PositionRepository::getPosition(
            coin_pair_id: $orderData->coinPair->id ?? 0,
            user_id: $this->user_id
        );

        if (
            $openPosition &&
            $orderData->order_type !== $openPosition?->order_type &&
            $openPosition->order_type == OrderType::BUY
        ) {
            $this->sell_reduce_cost = LeverageMath::initial_margin(
                price: $openPosition->price,
                amount: $openPosition->amount,
                leverage: $openPosition->leverage,
                scale: $orderData->coinPair->trade_decimal
            );

            $this->total_margin = bcsubx($this->total_margin, $this->sell_reduce_cost, $orderData->coinPair->trade_decimal ?: 8);
        }

        if (
            $openPosition &&
            $orderData->order_type !== $openPosition?->order_type &&
            $openPosition->order_type == OrderType::SELL
        ) {
            $this->buy_reduce_cost = LeverageMath::initial_margin(
                price: $openPosition->price,
                amount: $openPosition->amount,
                leverage: $openPosition->leverage,
                scale: $orderData->coinPair->trade_decimal
            );

            $this->total_margin = bcsubx($this->total_margin, $this->buy_reduce_cost, $orderData->coinPair->trade_decimal ?: 8);
        }
    }

    public function position_close_cost()
    {
        $positionCloseFees = FuturePosition::query()
        ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_positions.future_coin_pair_id')
        ->where('future_positions.user_id', $this->user_id)
        ->where('future_positions.amount', '<>', 0)
        ->where('future_positions.status', PositionStatusEnum::OPEN->value)
        ->selectRaw(
            'SUM(
                (future_positions.price * future_positions.amount)
                * (future_coin_pairs.taker_fees_percent / 100)
            ) AS position_close_fees'
        )
        ->value('position_close_fees');

        $this->total_close_fees = $positionCloseFees ?? 0;

        return $this;
    }

    private function all_position_cost(?array $ignore_ids = null)
    {
        $query = FuturePosition::query()
            ->where('user_id', $this->user_id)
            ->where('amount', '<>', 0)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->when($ignore_ids, fn($q) => $q->whereNotIn('id', $ignore_ids));

        // $totalInitialMargin query on this line then limit 1 query get add to $query
        // next line all position not return, 1 position return by limit 1 even many position exist on db
        // this comment just for remember the issue

        $positions = $query
            ->with(['coinPair:id,trade_decimal,taker_fees_percent,maker_fees_percent'])
            ->whereHas('coinPair')
            ->select('*')
            ->selectRaw('(price * ABS(amount)) / leverage AS initial_margin')
            ->where('margin_mode', MarginModeEnum::CROSS->value)
            ->get();

        $totalInitialMargin = FuturePosition::query()
            ->where('user_id', $this->user_id)
            ->where('amount', '<>', 0)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->when($ignore_ids, fn($q) => $q->whereNotIn('id', $ignore_ids))
            ->selectRaw('SUM((price * ABS(amount)) / leverage) AS total_initial_margin')
            ->value('total_initial_margin');

        $this->total_margin = $totalInitialMargin ?? 0;

        foreach ($positions as $position) {
            $mark_price = cache_service()->getMarkPrice($position->coinPair->id);

            $position_pnl = PositionMath::getPnl(
                mark_price: $mark_price,
                entryPrice: $position->price,
                position_size: abs($position->amount),
                direction: $position->order_type,
                scale: $position->coinPair->trade_decimal
            );

            $this->total_pnl = bcaddx($position_pnl, $this->total_pnl, $position->coinPair->trade_decimal ?: 8);
        }
    }
}
