<?php

namespace Modules\FutureTrade\Services\LiquidationServices;

use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;
use Modules\FutureTrade\Repositories\PositionRepository\IPositionRepository;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;
use Modules\FutureTrade\Services\NotificationServices\NotificationService;

class LiquidationService
{
    public function __construct(
        private FuturePosition $position,
        private FutureCoinPair $coinPair,
        private string|float $mark_price,
        private int $user_id,
        private MarginModeEnum $margin_mode,
    ){}

    public function liquidate()
    {
        $margin_mode = $this->position->margin_mode;
        if($margin_mode == MarginModeEnum::CROSS){
            $closed = $this->cancelAllOpenPosition();
            $this->cancelAllPendingOrders();
            if ($closed) {
                NotificationService::positionLiquidated(
                    $this->user_id,
                    $this->coinPair?->code,
                    $this->position?->uid,
                    $this->margin_mode
                );
            }

            return;
        }

        $closed = $this->cancelOpenPosition();
        if ($closed) {
            NotificationService::positionLiquidated(
                $this->user_id,
                $this->coinPair?->code,
                $this->position?->uid,
                $this->margin_mode
            );
        }
        // $this->cancelPendingOrder();
    }

    public function cancelAllPendingOrders()
    {
        $buyOrders = app(IOrderRepository::class)
            ->getPendingOrdersBuilder($this->coinPair->id, OrderType::BUY)
            ->get();

        if($buyOrders->isNotEmpty()){
            $buyOrders->map(function($order){
                $order->update([
                    'status' => OrderStatusEnum::CANCEL->value
                ]);
            });
        }

        $sellOrders = app(IOrderRepository::class)
            ->getPendingOrdersBuilder($this->coinPair->id, OrderType::SELL)
            ->get();

        if($sellOrders->isNotEmpty()){
            $sellOrders->map(function($order){
                $order->update([
                    'status' => OrderStatusEnum::CANCEL->value
                ]);
            });
        }
    }

    public function cancelPendingOrder()
    {

    }

    public function cancelAllOpenPosition(): bool
    {
        $openPositions = app(IPositionRepository::class)
            ->getAllOpenPosition($this->user_id)
            ->where('margin_mode', $this->margin_mode->value)
            ->get();

        if($openPositions->isEmpty()){
            return false;
        }

        $positionService = app(FuturePositionService::class);
        $openPositions->map(function($position) use ($positionService){
            $positionService->closePosition($position);
        });

        return true;
    }

    public function cancelOpenPosition(): bool
    {
        $position = $this->position;
        if (!$position) {
            return false;
        }
        $positionService = app(FuturePositionService::class);
        $positionService->closePosition($position);

        return true;
    }
}
