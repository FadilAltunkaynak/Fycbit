<?php

namespace Modules\FutureTrade\Observers;

use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\PositionBroadcastEventEnum;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Events\PositionBroadcastEvent;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;
use Modules\FutureTrade\Services\MathService\PositionMath;


class FuturePositionObserver
{
    /**
     * Handle the FuturePosition "created" event.
     *
     * @param  FuturePosition  $futureTrade
     * @return void
     */
    public function created(FuturePosition $futurePosition)
    {
        rescue(function () use ($futurePosition) {
            $service = app(FuturePositionService::class);

            $positions = FuturePosition::query()
                ->select('future_positions.*')
                ->getLeverageSetting()
                ->with(['coinPair'])
                ->where('future_positions.id', $futurePosition->id)
                ->where('future_positions.amount', '<>', 0)
                ->where('future_positions.status', PositionStatusEnum::OPEN->value)
                ->first();
            
            $data = $service->getPositionInfo($positions);

            if ($data) {
                PositionBroadcastEvent::dispatch(
                    $futurePosition->user_id,
                    $data,
                );

                $service->broadcastUserTradingState($futurePosition->user_id, $futurePosition->coinPair->uid);
            }
        });
    }

    /**
     * Handle the FuturePosition "updated" event.
     *
     * @param  FuturePosition  $futurePosition
     * @return void
     */
    public function updated(FuturePosition $futurePosition)
    {
        rescue(function () use ($futurePosition) {
            if ($futurePosition->amount == 0)
                return;
            $service = app(FuturePositionService::class);

            $positions = FuturePosition::query()
                ->select('future_positions.*')
                ->getLeverageSetting()
                ->with(['coinPair'])
                ->where('future_positions.id', $futurePosition->id)
                ->where('future_positions.amount', '<>', 0)
                ->where('future_positions.status', PositionStatusEnum::OPEN->value)
                ->first();

            $data = $service->getPositionInfo($positions);

            if ($data) {
                PositionBroadcastEvent::dispatch(
                    $futurePosition->user_id,
                    $data,
                );

                $service->broadcastUserTradingState($futurePosition->user_id, $futurePosition->coinPair->uid);
            }
        });
    }

    /**
     * Handle the FuturePosition "deleted" event.
     *
     * @param  FuturePosition  $futurePosition
     * @return void
     */
    public function deleted(FuturePosition $futurePosition)
    {
        rescue(function () use ($futurePosition) {
            $service = app(FuturePositionService::class);

            $futurePosition->load(['coinPair']);
            $data = [
                'uid' => $futurePosition->uid,
                'code' => $futurePosition->coinPair?->code,
                'price' => $futurePosition->price,
                'amount' => 0,
                'mark_price' => 0,
                'pnl' => 0,
                'roi' => 0,
                'ratio' => 0,
                'liquidation_price' => 0,
                'margin' => 0,
                'tp_price' => 0,
                'sl_price' => 0,
                'leverage' => $futurePosition->leverage,
                'margin_mode' => $futurePosition->margin_mode?->value ?: 1,
                'order_type' => $futurePosition->order_type?->value ?: 1,
                'base_coin_code' => $futurePosition?->coinPair?->base_coin_code ?? 'BTC',
                'trade_coin_code' => $futurePosition?->coinPair?->trade_coin_code ?? 'USDT',
                'base_decimal' => $futurePosition?->coinPair?->base_decimal ?? 8,
                'trade_decimal' => $futurePosition?->coinPair?->trade_decimal ?? 8,
                'coin_pair_uid' => $futurePosition?->coinPair?->uid,
                'status' => PositionStatusEnum::CLOSED->value
            ];

            PositionBroadcastEvent::dispatch(
                $futurePosition->user_id,
                $data,
            );

            $service->broadcastUserTradingState($futurePosition->user_id, $futurePosition->coinPair->uid);
        });
    }

    /**
     * Handle the FuturePosition "restored" event.
     *
     * @param  FuturePosition  $futurePosition
     * @return void
     */
    public function restored(FuturePosition $futurePosition)
    {
        //
    }

    /**
     * Handle the FuturePosition "force deleted" event.
     *
     * @param  FuturePosition  $futurePosition
     * @return void
     */
    public function forceDeleted(FuturePosition $futurePosition)
    {
        //
    }
}
