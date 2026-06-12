<?php

namespace Modules\FutureTrade\Services\OrderService;

use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FuturePosition;

class OrderSummaryService
{
    /**
     * Get summary of open orders and positions for authenticated user
     *
     * @param int|null $userId
     * @return array{success: bool, message: string, data: array}
     */
    public function getOpenOrdersAndPositionsSummary(string $future_coin_pair_uid = '0', ?int $userId = null,): array
    {
        try {
            $userId = $userId ?? authId();

            $openBuyTotal = $this->getOpenBuyOrderTotal($userId, $future_coin_pair_uid);
            $openSellTotal = $this->getOpenSellOrderTotal($userId, $future_coin_pair_uid);
            $openPositionsTotal = $this->getOpenPositionsTotal($userId, $future_coin_pair_uid);

            return [
                'open_buy_total_amount' => $openBuyTotal,
                'open_sell_total_amount' => $openSellTotal,
                'open_positions_total_amount' => $openPositionsTotal,
                // 'summary' => [
                //     'total_open_orders' => bcaddx($openBuyTotal, $openSellTotal, 8),
                //     'total_open_amount' => bcaddx(bcaddx($openBuyTotal, $openSellTotal, 8), $openPositionsTotal, 8),
                // ]
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total amount of open buy orders for a user
     *
     * @param int $userId
     * @return string
     */
    private function getOpenBuyOrderTotal(int $userId, string $uid = '0'): string
    {
        $total = FutureBuy::where('user_id', $userId)
            ->where('uid', $uid)
            ->where('status', OrderStatusEnum::PENDING->value)
            ->select(DB::raw('COALESCE(SUM(pending_amount), 0) as total'))
            ->first()
            ->total ?? 0;

        return trim_num((string) $total) ?: '0';
    }

    /**
     * Get total amount of open sell orders for a user
     *
     * @param int $userId
     * @return string
     */
    private function getOpenSellOrderTotal(int $userId, string $uid = '0'): string
    {
        $total = FutureSell::where('user_id', $userId)
            ->where('uid', $uid)
            ->where('status', OrderStatusEnum::PENDING->value)
            ->select(DB::raw('COALESCE(SUM(pending_amount), 0) as total'))
            ->first()
            ->total ?? 0;

        return trim_num((string) $total) ?: '0';
    }

    /**
     * Get total amount of open positions for a user
     *
     * @param int $userId
     * @return string
     */
    private function getOpenPositionsTotal(int $userId, string $future_coin_pair_uid = '0'): string
    {
        $total = FuturePosition::where('user_id', $userId)
            ->where('future_coin_pair_uid', $future_coin_pair_uid)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->select(DB::raw('COALESCE(amount, 0) as total'))
            ->first()
            ->total ?? 0;

        return trim_num((string) $total) ?: '0';
    }

    /**
     * Get detailed summary of open orders and positions by coin pair
     *
     * @param int|null $userId
     * @return array{success: bool, message: string, data: array}
     */
    public function getDetailedOpenOrdersAndPositionsSummary(?int $userId = null): array
    {
        try {
            $userId = $userId ?? authId();

            // Get open buy orders by coin pair
            $buyOrdersByPair = FutureBuy::where('user_id', $userId)
                ->where('status', OrderStatusEnum::PENDING->value)
                ->groupBy('future_coin_pair_id')
                ->select(
                    'future_coin_pair_id',
                    DB::raw('COALESCE(SUM(pending_amount), 0) as total_amount'),
                    DB::raw('COALESCE(SUM(pending_amount * price), 0) as total_value'),
                    DB::raw('COUNT(*) as order_count')
                )
                ->with('coinPair')
                ->get()
                ->map(function ($item) {
                    return [
                        'coin_pair_id' => $item->future_coin_pair_id,
                        'coin_pair_code' => $item->coinPair->base_coin_code . '/' . $item->coinPair->trade_coin_code,
                        'total_amount' => trim_num((string) $item->total_amount),
                        'total_value' => trim_num((string) $item->total_value),
                        'order_count' => $item->order_count,
                        'type' => 'buy'
                    ];
                });

            // Get open sell orders by coin pair
            $sellOrdersByPair = FutureSell::where('user_id', $userId)
                ->where('status', OrderStatusEnum::PENDING->value)
                ->groupBy('future_coin_pair_id')
                ->select(
                    'future_coin_pair_id',
                    DB::raw('COALESCE(SUM(pending_amount), 0) as total_amount'),
                    DB::raw('COALESCE(SUM(pending_amount * price), 0) as total_value'),
                    DB::raw('COUNT(*) as order_count')
                )
                ->with('coinPair')
                ->get()
                ->map(function ($item) {
                    return [
                        'coin_pair_id' => $item->future_coin_pair_id,
                        'coin_pair_code' => $item->coinPair->base_coin_code . '/' . $item->coinPair->trade_coin_code,
                        'total_amount' => trim_num((string) $item->total_amount),
                        'total_value' => trim_num((string) $item->total_value),
                        'order_count' => $item->order_count,
                        'type' => 'sell'
                    ];
                });

            // Get open positions by coin pair
            $positionsByPair = FuturePosition::where('user_id', $userId)
                ->where('status', PositionStatusEnum::OPEN->value)
                ->groupBy('future_coin_pair_id')
                ->select(
                    'future_coin_pair_id',
                    DB::raw('COALESCE(SUM(amount), 0) as total_amount'),
                    DB::raw('COALESCE(SUM(amount * price), 0) as total_value'),
                    DB::raw('COUNT(*) as position_count')
                )
                ->with('coinPair')
                ->get()
                ->map(function ($item) {
                    return [
                        'coin_pair_id' => $item->future_coin_pair_id,
                        'coin_pair_code' => $item->coinPair->base_coin_code . '/' . $item->coinPair->trade_coin_code,
                        'total_amount' => trim_num((string) $item->total_amount),
                        'total_value' => trim_num((string) $item->total_value),
                        'position_count' => $item->position_count,
                        'type' => 'position'
                    ];
                });

            $data = [
                'open_buy_orders' => $buyOrdersByPair,
                'open_sell_orders' => $sellOrdersByPair,
                'open_positions' => $positionsByPair,
                'summary' => [
                    'total_buy_orders_count' => $buyOrdersByPair->sum('order_count'),
                    'total_sell_orders_count' => $sellOrdersByPair->sum('order_count'),
                    'total_positions_count' => $positionsByPair->sum('position_count'),
                    'total_buy_value' => trim_num((string) $buyOrdersByPair->sum('total_value')),
                    'total_sell_value' => trim_num((string) $sellOrdersByPair->sum('total_value')),
                    'total_positions_value' => trim_num((string) $positionsByPair->sum('total_value')),
                ]
            ];

            return [
                'success' => true,
                'message' => __('Detailed summary retrieved successfully'),
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Error retrieving detailed summary'),
                'data' => []
            ];
        }
    }
}
