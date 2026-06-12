<?php

namespace Modules\FutureTrade\Services\FuturePositionServices;

use App\Facades\ResponseFacade;
use App\Model\Coin;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\DataObject\FuturePositionData;
use Modules\FutureTrade\Emum\DefaultMarginValue;
use Modules\FutureTrade\Emum\IsolatedMarginUpdateActionEnum;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionBroadcastEventEnum;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Emum\TpSlType;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FuturePositionHistory;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Events\PositionBroadcastEvent;
use Modules\FutureTrade\Events\PrivateMarginSummeryBroadcastEvent;
use Modules\FutureTrade\Events\PrivateOrderSummaryBroadcastEvent;
use Modules\FutureTrade\Events\PrivateUserAssetMarginBroadcastEvent;
use Modules\FutureTrade\Events\PrivateUserWalletBroadcastEvent;
use Modules\FutureTrade\Http\Requests\Api\FuturePositionHistoryFilterRequest;
use Modules\FutureTrade\Http\Requests\Api\PositionTpSlCancelRequest;
use Modules\FutureTrade\Http\Requests\Api\PositionTpSlUpdateRequest;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;
use Modules\FutureTrade\Repositories\PositionRepository\IPositionRepository;
use Modules\FutureTrade\Repositories\PositionRepository\PositionRepository;
use Modules\FutureTrade\Repositories\WalletRepository\FutureWalletRepository;
use Modules\FutureTrade\Services\CostService\CostService;
use Modules\FutureTrade\Services\CostService\OrderCost;
use Modules\FutureTrade\Services\LiquidationServices\LiquidationService;
use Modules\FutureTrade\Services\MathService\PositionMath;
use Modules\FutureTrade\Services\NotificationServices\NotificationService;
use Modules\FutureTrade\Services\OrderService\OrderSummaryService;
use Modules\FutureTrade\Services\OrderService\OrderService;
use Modules\FutureTrade\Services\WalletServices\WalletService;

class FuturePositionService
{
    public function __construct(
        public IPositionRepository $repository
    ) {
    }

    /**
     * Create New Position
     */
    public function createPosition(FuturePositionData $data): ?FuturePosition
    {
        $position = rescue(function () use ($data): ?FuturePosition {
            return $this->repository->create($data->toArray());
        });

        if (!$position) {
            return null;
        }

        $mark_price = (string) cache_service()->getMarkPrice($data->coinPair->id);

        $positionHistory = $this->createPositionHistory(
            position: $position,
            coinPair: $data->coinPair,
            mark_price: $mark_price,
            status: PositionStatusEnum::OPEN
        );

        if (!$positionHistory) {
            return null;
        }

        return $position;
    }

    public function createPositionHistory(
        FuturePosition $position,
        FutureCoinPair $coinPair,
        string|float $mark_price,
        PositionStatusEnum $status
    ): ?FuturePositionHistory {
        $pnl = PositionMath::getPnl(
            mark_price: $mark_price,
            entryPrice: $position->price,
            position_size: abs($position->amount),
            direction: $position->order_type,
            scale: $coinPair?->trade_decimal ?: 8,
        );

        $positionHistoryData = [
            'future_position_id' => $position->id,
            'user_id' => $position->user_id,
            'amount' => $position->amount,
            'margin_mode' => $position->margin_mode->value,
            'mark_price' => $mark_price,
            'price' => $position->price,
            'pnl' => $pnl,
            'leverage' => $position->leverage,
            'status' => $status->value,
        ];

        $history = rescue(fn() => FuturePositionHistory::create($positionHistoryData));

        if (!$history) {
            return null;
        }

        return $history;
    }

    public function processPosition(
        int $futureTradeId,
        FutureCoinPair $coinPair,
        FutureWallet $wallet,
        int $userId,
        OrderType $orderType
    ): void {
        $futureTrade = FutureTrade::with('coin_pair')->find($futureTradeId);

        if (!$futureTrade) {
            debugLogger("Future Trade record not found in position process, id $futureTradeId");

            return;
        }

        $futureTrade->orderType = $orderType;
        $futureTrade->coinPair = $futureTrade->coin_pair;

        $currentPosition = $this->repository->getPosition(
            coin_pair_id: $futureTrade->future_coin_pair_id,
            user_id: $userId
        );

        $futureTrade->coinPair = $coinPair;
        $positionData = FuturePositionData::fromTrade($futureTrade, $wallet);

        if (!$currentPosition) {
            $newPosition = $this->createPosition($positionData);
            NotificationService::positionOpen($userId);
            debugLogger('new position created');

            return;
        }

        $mark_price = (string) cache_service()->getMarkPrice($coinPair->id);
        $positionSame = $currentPosition->order_type == $futureTrade->orderType;
        if ($positionSame) {
            $newEntryPrice = PositionMath::entryPrice(
                new_position: $positionData,
                current_position: $currentPosition
            );

            $newAmount = bcaddx($currentPosition->amount, $positionData->amount, ($positionData?->coinPair?->base_decimal ?? 8) + 10);

            $positionData->price = $newEntryPrice;
            $positionData->amount = $newAmount;

            $currentPosition->update([
                'price' => $newEntryPrice,
                'amount' => $newAmount,
                'margin_balance' => PositionMath::initialMargin($positionData),
            ]);

            $positionHistory = $this->createPositionHistory(
                position: $currentPosition,
                coinPair: $coinPair,
                mark_price: $mark_price,
                status: PositionStatusEnum::UPDATED
            );

            if (!$positionHistory) {
                return;
            }

            debugLogger('Position amount increased');

            return;
        }

        $next_amount = 0;
        $updateData = [];

        $order = app(IOrderRepository::class)->getOrderById(
            orderId: $futureTrade->orderType == OrderType::BUY ? $futureTrade->buy_id : $futureTrade->sell_id,
            orderType: $futureTrade->orderType
        )->first();

        if ($order?->is_reduce) {
            if ($currentPosition->amount == 0) {
                debugLogger('Position amount already 0, Reduce only order');

                return;
            }

            if ($positionData->amount > abs($currentPosition->amount)) {
                $positionData->amount = $currentPosition->amount;
            }
        }

        $amountShouldBeClose = PositionMath::positionCloseAmount(
            newPosition: $positionData,
            current_position: $currentPosition
        );

        $positionData->order_type = null;
        if (!($amountShouldBeClose == 0)) {
            $next_amount = bcaddx($currentPosition->amount, $positionData->amount, ($positionData?->coinPair?->base_decimal ?? 8) + 10);
            if ($next_amount < 0) {
                $positionData->order_type = OrderType::SELL;
                $updateData = ['price' => $positionData->price];
            } elseif ($next_amount > 0) {
                $positionData->order_type = OrderType::BUY;
                $updateData = ['price' => $positionData->price];
            }
        }
        try{
            $currentPosition->increment('amount', $positionData->amount, $positionData->toArrayForUpdate($updateData));

            if ($currentPosition->amount == 0) {
                // $currentPosition->update(['status' => PositionStatusEnum::CLOSED->value]);
                $currentPosition->amount = $positionData->amount;
                $this->updatePositionToUserWallet($currentPosition);

                $deleted = $currentPosition->delete();
                NotificationService::positionClosed($userId);

                $positionHistory = $this->createPositionHistory(
                    position: $currentPosition,
                    coinPair: $coinPair,
                    mark_price: $mark_price,
                    status: PositionStatusEnum::CLOSED
                );

                if (!$positionHistory) {
                    return;
                }
            } else {
                $currentPosition->update([
                    'margin_balance' => PositionMath::initialMargin($currentPosition),
                ]);
                $positionHistory = $this->createPositionHistory(
                    position: $currentPosition,
                    coinPair: $coinPair,
                    mark_price: $mark_price,
                    status: PositionStatusEnum::UPDATED
                );

                if (!$positionHistory) {
                    return;
                }
            }
        }catch(\Throwable $e){
            debugLogger('position process: '.$e->getMessage());
        }
        debugLogger('position process closed');
    }

    public function processPositionWithMarkPrice(
        FutureCoinPair $coin_pair,
        string|float $mark_price
    ) {
        $coin_pair->mark_price = $mark_price;

        $positions = FuturePosition::query()
            ->join(
                'future_leverage_settings',
                'future_leverage_settings.coin_pair_uid',
                '=',
                'future_positions.future_coin_pair_uid'
            )
            ->where('future_positions.future_coin_pair_id', $coin_pair->id)
            ->where('future_positions.status', PositionStatusEnum::OPEN->value)
            ->whereRaw( // TODO FIL: if total amount out of range of min and max position amount, this query may ignore the position to process
                'ABS(future_positions.amount) BETWEEN future_leverage_settings.min_position_amount AND future_leverage_settings.max_position_amount'
            )
            ->select(
                'future_positions.*',
                'future_leverage_settings.maintenance_amount',
                'future_leverage_settings.maintenance_margin_rate',
            )
            ->get();

        if ($positions->isEmpty()) {
            return;
        }

        $this->processCrossPosition(
            coin_pair: $coin_pair,
            mark_price: $mark_price,
            positions: $positions
        );

        $this->processIsolatedPosition(
            coin_pair: $coin_pair,
            mark_price: $mark_price,
            positions: $positions
        );
    }

    public function processCrossPosition(
        FutureCoinPair $coin_pair,
        string|float $mark_price,
        Collection $positions
    ) {
        $positions = $positions->where('margin_mode', MarginModeEnum::CROSS);

        if ($positions->isEmpty()) {
            return;
        }

        $positionByUsers = $positions->groupBy('user_id');

        foreach ($positionByUsers as $positions) {
            $this->crossMarginCalculationUpdate($coin_pair, $positions);
            foreach ($positions as $position) {
                $position->coin_pair = $coin_pair;
                $this->processTpSL_position($position, $mark_price);
            }
        }
    }

    public function processIsolatedPosition(
        FutureCoinPair $coin_pair,
        string|float $mark_price,
        Collection $positions
    ) {
        $positions = $positions->where('margin_mode', MarginModeEnum::ISOLATED);

        if ($positions->isEmpty()) {
            return;
        }

        foreach ($positions as $position) {
            $this->isolatedMarginCalculationUpdate($coin_pair, $positions, $position);
            $position->coin_pair = $coin_pair;
            $this->processTpSL_position($position, $mark_price);
        }
    }

    public function crossMarginCalculationUpdate(FutureCoinPair $coin_pair, Collection $crossPositions)
    {
        $userCrossPositions = $crossPositions;
        $user = User::find($userCrossPositions[0]?->user_id ?? 0);
        if (!$user) {
            return;
        }

        $wallet = FutureWallet::where([
            'user_id' => $user->id,
            'coin_id' => $coin_pair->trade_coin_id
        ])->first();

        $maintenance_margin = $this->getMaintenanceMargin($coin_pair, $userCrossPositions);

        [$marginBalance, $total_isolated_balance, $cross_balance_liq]
            = $this->getMarginBalance($coin_pair, $userCrossPositions, $coin_pair->mark_price);

        $margin_ratio = PositionMath::getMarginRation($maintenance_margin, $marginBalance, $coin_pair?->trade_decimal ?: 8);

        $marginSummery = [
            'maintenance_margin' => $maintenance_margin,
            'margin_balance' => $marginBalance,
            'wallet_balance' => $wallet?->balance ?? 0,
            'margin_ratio' => $margin_ratio,
        ];

        rescue(fn() => PrivateMarginSummeryBroadcastEvent::dispatch(
            $user->id,
            $coin_pair->uid,
            $marginSummery
        ));

        $userAssetSummery = $this->getUserAssetsSummary($userCrossPositions[0], $user->id);
        rescue(fn() => PrivateUserAssetMarginBroadcastEvent::dispatch(
            $user->id,
            $coin_pair->uid,
            $userAssetSummery
        ));
        $this->broadcastUserWalletDetails($user->id);

        if ($margin_ratio >= 80 && $margin_ratio < 100) {
            NotificationService::positionLiquidationWarning($user->id);

            return;
        }

        if ($margin_ratio >= 100) {
            $liquidationService = new LiquidationService(
                position: $userCrossPositions[0],
                coinPair: $coin_pair,
                mark_price: $coin_pair->mark_price,
                user_id: $user->id,
                margin_mode: MarginModeEnum::CROSS
            );

            $liquidationService->liquidate();
        }
    }

    public function isolatedMarginCalculationUpdate(FutureCoinPair $coin_pair, Collection $isolatedPositions, FuturePosition $position)
    {
        $decimal = $coin_pair?->trade_decimal ?: 8;
        $user = User::find($position->user_id ?? 0);
        if (!$user) {
            return;
        }

        $userIsolatedPositions = $isolatedPositions->where('user_id', $position->user_id);

        $pnl = PositionMath::getPnl(
            mark_price: $coin_pair->mark_price,
            entryPrice: $position->price,
            position_size: abs($position->amount),
            direction: $position->order_type,
            scale: $decimal
        );

        $position_margin_balance = bcaddx($position->margin_balance, $pnl, $decimal);
        $maintenance_margin = PositionMath::getMaintenanceMargin(
            price: $position->price,
            amount: abs($position->amount),
            maintenance_margin_rate: $position?->leverageSetting->maintenance_margin_rate ?? DefaultMarginValue::MARGIN_RATE->value,
            maintenance_amount: $position?->leverageSetting->maintenance_amount ?? DefaultMarginValue::MARGIN_AMOUNT->value,
            scale: $decimal
        );

        $margin_ratio = PositionMath::getMarginRation(
            $maintenance_margin,
            $position_margin_balance,
            $decimal
        );

        $marginSummery = [
            'maintenance_margin' => $maintenance_margin,
            'margin_balance' => $position_margin_balance,
            'wallet_balance' => $position?->future_wallet?->balance ?? 0,
            'margin_ratio' => $margin_ratio,
        ];

        rescue(fn() => PrivateMarginSummeryBroadcastEvent::dispatch(
            $user->id,
            $coin_pair->uid,
            $marginSummery
        ));

        $userAssetSummery = $this->getUserAssetsSummary($position, $user->id);
        rescue(fn() => PrivateUserAssetMarginBroadcastEvent::dispatch(
            $user->id,
            $coin_pair->uid,
            $userAssetSummery
        ));
        $this->broadcastUserWalletDetails($user->id);

        $posInfo = $this->getPositionInfo($position);
        rescue(fn() => PositionBroadcastEvent::dispatch(
            $user->id,
            $posInfo
        ));

        if ($margin_ratio >= 80 && $margin_ratio < 100) {
            NotificationService::positionLiquidationWarning($user->id);
        }

        if ($margin_ratio >= 100) {
            $liquidation_amount = $position->margin_balance;

            $liquidationService = new LiquidationService(
                position: $userIsolatedPositions->first(),
                coinPair: $coin_pair,
                mark_price: $coin_pair->mark_price,
                user_id: $user->id,
                margin_mode: MarginModeEnum::ISOLATED
            );

            $liquidationService->liquidate();
        }
    }

    public function getMaintenanceMargin(FutureCoinPair $coin_pair, Collection $positions): int|string
    {
        $total_margin = 0;

        $positions->map(function ($position) use ($coin_pair, &$total_margin) {
            $total_margin = PositionMath::getMaintenanceMargin(
                price: $position->price,
                amount: abs($position->amount),
                maintenance_margin_rate: $position->maintenance_margin_rate ?? DefaultMarginValue::MARGIN_RATE->value,
                maintenance_amount: $position->maintenance_amount ?? DefaultMarginValue::MARGIN_AMOUNT->value,
                scale: $coin_pair->trade_decimal ?? 8
            );
        });

        return $total_margin;
    }

    /**
     * Get Margin Balance For Cross And Isolated Position
     *
     * @return array{margin:string,total_isolated_balance:string,cross_balance_liq:string}
     */
    public function getMarginBalance(
        FutureCoinPair $coin_pair,
        Collection $positions,
        string|float $mark_price,
    ): array {
        $wallet = FutureWallet::where([
            'user_id' => $positions[0]?->user_id ?? 0,
            'coin_id' => $positions[0]?->trade_coin_id ?? 0
        ])->first();

        $wallet_balance = $wallet?->balance ?? 0;
        $cross_pnl = 0;
        $isolated_pnl = 0;
        $isolated_balance = 0;

        $positions?->map(function ($position) use ($coin_pair, &$isolated_balance, &$cross_pnl, &$isolated_pnl, $mark_price) {
            $pnl = PositionMath::getPnl(
                mark_price: $mark_price,
                entryPrice: $position->price,
                position_size: abs($position->amount),
                direction: $position->order_type,
                scale: $coin_pair->trade_decimal ?? 8,
            );

            $posInfo = $this->getPositionInfo($position);
            rescue(fn() => PositionBroadcastEvent::dispatch(
                $position->user_id,
                $posInfo,
            ));

            if ($position->margin_mode == MarginModeEnum::CROSS) {
                $cross_pnl = bcaddx($cross_pnl, $pnl, $coin_pair->trade_decimal ?? 8);

                return;
            }

            $isolated_pnl = bcaddx($isolated_pnl, $pnl, $coin_pair->trade_decimal ?? 8);
            $isolated_balance = $position->margin_balance;
        });

        $margin = bcaddx(
            bcsubx($wallet_balance, $isolated_balance, $coin_pair->trade_decimal ?? 8),
            $cross_pnl,
            $coin_pair->trade_decimal ?? 8
        );

        $total_isolated_balance = bcaddx($isolated_balance, $isolated_pnl, $coin_pair->trade_decimal ?? 8);
        $cross_balance_liq = bcsubx($wallet_balance, $total_isolated_balance, $coin_pair->trade_decimal ?? 8);

        return [
            $margin,
            $total_isolated_balance,
            $cross_balance_liq,
        ];
    }

    public function processTpSL_position(
        FuturePosition $position,
        string|float $mark_price
    ) {
        $buyPosition = $position->order_type == OrderType::BUY;
        $buyTpPosition = $buyPosition && $position->tp_price > 0 && $mark_price >= $position->tp_price;
        $buySLPosition = $buyPosition && $position->sl_price > 0 && $mark_price <= $position->sl_price;

        $sellPosition = $position->order_type == OrderType::SELL;
        $sellTpPosition = $sellPosition && $position->tp_price > 0 && $mark_price <= $position->tp_price;
        $sellSLPosition = $sellPosition && $position->sl_price > 0 && $mark_price >= $position->sl_price;

        $service = app(OrderService::class);
        match (true) {
            $buyTpPosition => $service->tpSl_Order($position, TpSlType::TAKE_PROFIT, $mark_price),
            $buySLPosition => $service->tpSl_Order($position, TpSlType::STOP_LOSS, $mark_price),
            $sellTpPosition => $service->tpSl_Order($position, TpSlType::TAKE_PROFIT, $mark_price),
            $sellSLPosition => $service->tpSl_Order($position, TpSlType::STOP_LOSS, $mark_price),
            default => ''
        };
    }

    public function closePosition(FuturePosition $position)
    {
        $position->loadMissing('coinPair');
        $coinPair = $position->coinPair;
        if (!$coinPair) {
            debugLogger('position close failed: coin pair not found');
            return;
        }

        $mark_price = (string) cache_service()->getMarkPrice($coinPair->id);
        $positionUpdateData = [
            'amount' => 0,
            'status' => PositionStatusEnum::CLOSED->value,
            'tp_price' => 0,
            'sl_price' => 0,
            'margin_balance' => 0,
        ];

        DB::beginTransaction();
        try{
            // $updated = $position->update($positionUpdateData);
            $updated = $position->delete();

            if (!$updated) {
                debugLogger('position close failed');

                return;
            }
            $this->updatePositionToUserWallet($position);
        }catch(\Throwable $th){
            DB::rollBack();
            debugLogger('position close failed: ' . $th->getMessage());
            return;
        }

        DB::commit();

        $positionHistory = $this->createPositionHistory(
            position: $position,
            coinPair: $coinPair,
            mark_price: $mark_price,
            status: PositionStatusEnum::CLOSED
        );

        if (!$positionHistory) {
            debugLogger('position close history create failed');
        }
    }

    private function updatePositionToUserWallet(FuturePosition $position)
    {
        $mark_price = cache_service()->getMarkPrice($position->future_coin_pair_id);

        $pnl = PositionMath::getPnl(
            mark_price: $mark_price,
            entryPrice: $position->price,
            position_size: abs($position->amount),
            direction: $position->order_type,
            scale: 8,
        );

        $pnl_amount = abs($pnl);

        if (!($pnl_amount > 0)) {
            debugLogger("position wallet update pnl amount is $pnl_amount, pnl is $pnl");

            return;
        }

        debugLogger('future wallet lock from position close');
        $wallet = FutureWallet::where([
            'user_id' => $position->user_id,
            'coin_id' => $position->trade_coin_id
        ])->lockForUpdate()->first();

        if (!$wallet) {
            debugLogger('position close wallet update: Wallet not found');

            return;
        }

        $update = $wallet->increment('balance', $pnl);

        if (!$update) {
            debugLogger('position close wallet update: Wallet increment update failed');

            return;
        }
    }

    /**
     * Add More Available Margin To Ongoing Isolated Position
     */
    public function addMarginOnIsolatedPosition(
        string $coin_pair_uid,
        string|float $amount,
        int $action,
        ?int $user_id = null,
    ): mixed {
        $user_id ??= authId();
        $coinPair = CoinPairRepository::getCoinPairByUid($coin_pair_uid);

        if (!$coinPair) {
            ResponseFacade::failed(__("Coin pair not found"))->safeThrow();
        }

        $actionEnum = IsolatedMarginUpdateActionEnum::tryFrom($action);
        if (!$actionEnum) {
            ResponseFacade::failed(__("Invalid action"))->safeThrow();
        }

        DB::beginTransaction();
        try{
            $openPosition = FuturePosition::where([
                'future_coin_pair_id' => $coinPair->id,
                'user_id' => $user_id,
                'margin_mode' => MarginModeEnum::ISOLATED->value,
                'status' => PositionStatusEnum::OPEN->value
            ])->lockForUpdate()->first();

            if (!$openPosition) {
                ResponseFacade::failed(__("Open Isolated Position Not Found"))->safeThrow();
            }

            $wallet = FutureWallet::where([
                'coin_id' => $coinPair->trade_coin_id,
                'user_id' => $openPosition->user_id
            ])->lockForUpdate()->first();

            if (!$wallet) {
                ResponseFacade::failed(__("Wallet Not Found"))->safeThrow();
            }

            if ($actionEnum === IsolatedMarginUpdateActionEnum::CREDIT) {
                $costService = new CostService($openPosition->user_id);
                $totalCost = $costService->getUserCost();

                $costWithRequestedAmount = bcaddx($totalCost, $amount, $coinPair->trade_decimal);

                if (!($wallet->balance >= $costWithRequestedAmount)) {
                    ResponseFacade::failed(__("You do not have enough found"))->safeThrow();
                }
            } else {
                $initialMargin = abs(PositionMath::initialMargin($openPosition));
                $removable = bcsubx($openPosition->margin_balance, $initialMargin, $coinPair->trade_decimal);
                if (bccompx($amount, $removable, $coinPair->trade_decimal) == 1) {
                    ResponseFacade::failed(__("Insufficient balance to remove"))->safeThrow();
                }
            }

            $updated = $actionEnum === IsolatedMarginUpdateActionEnum::CREDIT
                ? $openPosition->increment('margin_balance', $amount)
                : $openPosition->decrement('margin_balance', $amount);
            if (!$updated) {
                DB::rollBack();
                ResponseFacade::failed(__("Margin failed to update on position"))->safeThrow();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            ResponseFacade::failed(__("Margin failed to update on position"))->safeThrow();
        }
        DB::commit();

        rescue(function () use ($user_id, $openPosition) {
            $openPosition->refresh();
            $openPosition->load(['coinPair']);
            $data = $this->getPositionInfo($openPosition);
            PositionBroadcastEvent::dispatch($user_id, $data);
            $this->broadcastUserTradingState($user_id, $openPosition->coinPair->uid);
        });

        return success(__("Margin updated successfully"));
    }

    public function getMyOpenPositions(?int $user_id = null)
    {
        $user_id ??= authId();

        // $positions = FuturePosition::with(['coinPair', 'leverageSetting', 'future_wallet'])
        //     ->where('user_id', $user_id)
        //     ->where('amount', '<>', 0)
        //     ->where('status', PositionStatusEnum::OPEN->value)
        //     ->get();

        $positions = FuturePosition::query()
            ->select('future_positions.*')
            ->getLeverageSetting()
            ->with(['coinPair', 'future_wallet'])
            ->where('future_positions.user_id', $user_id)
            ->where('future_positions.amount', '<>', 0)
            ->where('future_positions.status', PositionStatusEnum::OPEN->value)
            ->get();

        if ($positions->isEmpty()) {
            return success([]);
        }

        $positionsData = [];

        foreach ($positions as $position) {
            $coinPair = $position->coinPair;
            if (!$coinPair) {
                continue;
            }

            $positionsInfo = $this->getPositionInfo($position);
            if ($positionsInfo) {
                $positionsData[] = $positionsInfo;
            }
        }

        return success($positionsData);
    }

    public function getPositionInfo(FuturePosition $position): array
    {
        $roi = 0;
        $coinPair = $position->coinPair;
        if (!$coinPair) {
            return [];
        }
        $markPrice = cache_service()->getMarkPrice($coinPair->id);
        $decimal = $coinPair->trade_decimal ?: 8;

        $pnl = PositionMath::getPnl(
            mark_price: $markPrice,
            entryPrice: $position->price,
            position_size: abs($position->amount),
            direction: $position->order_type,
            scale: $decimal
        );

        $margin = PositionMath::initialMargin(
            $position,
        );

        if ($pnl != 0 && $margin != 0) {
            $roi = PositionMath::getRoi(
                pnl: $pnl,
                margin: $margin,
                scale: $decimal
            );
        }

        $mm = PositionMath::getMaintenanceMargin(
            price: $position->price,
            amount: abs($position->amount),
            maintenance_margin_rate: $position->leverageSetting->maintenance_margin_rate ?? DefaultMarginValue::MARGIN_RATE->value,
            maintenance_amount: $position->leverageSetting->maintenance_amount ?? DefaultMarginValue::MARGIN_AMOUNT->value,
            scale: $decimal
        );

        $wallet = $position->future_wallet;
        $walletBalance = $wallet->balance ?? 0;

        $isolatedBalance = 0;
        $isolatedPnl = 0;
        $crossPnl = 0;
        $marginBalance = 0;

        if ($position->margin_mode === MarginModeEnum::CROSS) {
            $crossPnl = $pnl;
            $marginBalance = bcaddx(
                bcsubx($walletBalance, $isolatedBalance, $decimal),
                $crossPnl,
                $decimal
            );
        } else {
            $isolatedPnl = $pnl;
            $isolatedBalance = $position->margin_balance;
            $marginBalance = $isolatedBalance;

            $marginBalance = bcaddx($marginBalance, $isolatedPnl, $coinPair->trade_decimal ?? 8);
        }

        $maintenanceMarginRate = $position->leverageSetting->maintenance_margin_rate
            ?? DefaultMarginValue::MARGIN_RATE->value;

        if ($position->margin_mode === MarginModeEnum::ISOLATED) {
            $liquidationPrice = PositionMath::getLiquidationPriceForIsolated(
                order_type: $position->order_type,
                entry_price: $position->price,
                amount: $position->amount,
                margin_balance: $position->margin_balance,
                maintenance_margin_rate: $maintenanceMarginRate,
                scale: $decimal,
            );
        } else {
            $pnlExcludCurr = '0';
            $mmExcludCurr = '0';

            $otherPositions = FuturePosition::query()
                ->select('future_positions.*')
                ->getLeverageSetting()
                ->with(['coinPair'])
                ->where('future_positions.user_id', $position->user_id)
                // ->where('future_positions.id', '<>', $position->id)
                ->where('future_positions.status', PositionStatusEnum::OPEN->value)
                ->where('future_positions.amount', '<>', 0)
                ->get();

            foreach ($otherPositions as $otherPos) {
                $otherCoinPair = $otherPos->coinPair;
                if (!$otherCoinPair) {
                    continue;
                }

                $otherMarkPrice = cache_service()->getMarkPrice($otherCoinPair->id);
                $otherDecimal = $otherCoinPair->trade_decimal ?: 8;

                $otherPnl = PositionMath::getPnl(
                    mark_price: $otherMarkPrice,
                    entryPrice: $otherPos->price,
                    position_size: abs($otherPos->amount),
                    direction: $otherPos->order_type,
                    scale: $otherDecimal
                );
                $pnlExcludCurr = bcaddx($pnlExcludCurr, $otherPnl, $decimal);

                $otherMmRate = $otherPos->leverageSetting->maintenance_margin_rate
                    ?? DefaultMarginValue::MARGIN_RATE->value;
                $otherMmAmount = $otherPos->leverageSetting->maintenance_amount
                    ?? DefaultMarginValue::MARGIN_AMOUNT->value;

                $otherMm = PositionMath::getMaintenanceMargin(
                    price: $otherPos->price,
                    amount: abs($otherPos->amount),
                    maintenance_margin_rate: $otherMmRate,
                    maintenance_amount: $otherMmAmount,
                    scale: $otherDecimal
                );
                $mmExcludCurr = bcaddx($mmExcludCurr, $otherMm, $decimal);
            }

            $liquidationPrice = PositionMath::getLiquidationPriceForCross(
                order_type: $position->order_type,
                entry_price: $position->price,
                amount: $position->amount,
                wallet_balance: $walletBalance,
                maintenance_margin_exclud_curr: $mmExcludCurr,
                pnl_exclud_curr: $pnlExcludCurr,
                maintenance_margin_rate: $maintenanceMarginRate,
                scale: $decimal,
            );

        }

        $liquidationPrice = truncate_num($liquidationPrice, $decimal);
        $liquidationPrice = $liquidationPrice < 0 ? "-" : $liquidationPrice;

        $marginRatio = PositionMath::getMarginRation(
            maintenance_margin: $mm,
            margin_balance: $marginBalance,
            scale: $decimal
        );

        return [
            'uid' => $position->uid,
            'code' => $position->coinPair->code,
            'price' => truncate_num($position->price, $decimal),
            'amount' => truncate_num($position->amount, $decimal),
            'mark_price' => truncate_num($markPrice, $decimal),
            'pnl' => truncate_num($pnl, $decimal),
            'roi' => truncate_num($roi, $decimal),
            'ratio' => truncate_num($marginRatio, $decimal),
            'liquidation_price' => $liquidationPrice,
            'margin' => truncate_num($margin, $decimal),
            'tp_price' => truncate_num($position->tp_price, $decimal),
            'sl_price' => truncate_num($position->sl_price, $decimal),
            'leverage' => $position->leverage,
            'margin_mode' => $position->margin_mode?->value ?: 1,
            'order_type' => $position->order_type?->value ?: 1,
            'base_coin_code' => $position?->coinPair?->base_coin_code ?? 'BTC',
            'trade_coin_code' => $position?->coinPair?->trade_coin_code ?? 'USDT',
            'base_decimal' => $position?->coinPair?->base_decimal ?? 8,
            'trade_decimal' => $position?->coinPair?->trade_decimal ?? 8,
            'coin_pair_uid' => $position?->coinPair?->uid
        ];
    }

    /**
     * Get maintenance margin, margin balance and wallet balance for the user's
     * open position on a given coin pair (by code).
     *
     * - Isolated: maintenance margin + margin balance = Isolated position margin + Unrealized PnL.
     * - Cross: maintenance margin for this position; margin balance = Wallet - Sum(All Isolated Margins) + Cross Unrealized PnL.
     *
     * @return array{maintenance_margin:string,margin_balance:string,wallet_balance:string,margin_ratio:string}
     */
    public function getUserMarginSummary(string $coin_pair_uid, ?int $user_id = null): array
    {
        $user_id ??= authId();

        $coinPair = CoinPairRepository::getCoinPairByUid($coin_pair_uid);

        if (!$coinPair) {
            ResponseFacade::failed(__('Coin pair not found'))->safeThrow();
        }

        $coin = Coin::find($coinPair->trade_coin_id);

        if (!$coin) {
            ResponseFacade::failed(__('Coin not found'))->safeThrow();
        }

        $wallet = rescue(fn() => FutureWalletRepository::getWallet($coin->id, $user_id));

        if (!$wallet) {
            ResponseFacade::failed(__("Wallet not found"))->safeThrow();
        }

        $scale = $coinPair->trade_decimal ?? 8;
        $wallet_balance = $wallet->balance ?: '0';

        // $position = FuturePosition::query()
        //     ->with(['coinPair', 'leverageSetting'])
        //     ->where('user_id', $user_id)
        //     ->where('future_coin_pair_id', $coinPair->id)
        //     ->where('status', PositionStatusEnum::OPEN->value)
        //     ->where('amount', '<>', 0)
        //     ->first();
        $position = FuturePosition::query()
            ->select('future_positions.*')
            ->join('future_leverage_settings as fls', function ($join) {
                $join->on('future_positions.future_coin_pair_uid', '=', 'fls.coin_pair_uid')
                    ->whereRaw('(future_positions.price * ABS(future_positions.amount)) 
                            BETWEEN fls.min_position_amount 
                            AND fls.max_position_amount');
            })
            ->with(['coinPair', 'future_wallet'])
            ->where('future_positions.user_id', $user_id)
            ->where('future_positions.future_coin_pair_id', $coinPair->id)
            ->where('future_positions.amount', '<>', 0)
            ->where('future_positions.status', PositionStatusEnum::OPEN->value)
            ->first();

        $mark_price = cache_service()->getMarkPrice($coinPair->id) ?: '0';
        $maintenance_margin = '0';

        if (!$position) {
            return [
                'maintenance_margin' => 0,
                'margin_balance' => 0,
                'wallet_balance' => 0,
                'margin_ratio' => 0,
            ];
        }

        if ($position->margin_mode === MarginModeEnum::CROSS) {
            // $positions = FuturePosition::query()
            //     ->with(['coinPair', 'leverageSetting'])
            //     ->where('user_id', $user_id)
            //     ->where('status', PositionStatusEnum::OPEN->value)
            //     ->where('amount', '<>', 0)
            //     ->where('margin_mode', MarginModeEnum::CROSS->value)
            //     ->get();
            $positions = FuturePosition::query()
                ->select('future_positions.*')
                ->join('future_leverage_settings as fls', function ($join) {
                    $join->on('future_positions.future_coin_pair_uid', '=', 'fls.coin_pair_uid')
                        ->whereRaw('(future_positions.price * ABS(future_positions.amount)) 
                                    BETWEEN fls.min_position_amount 
                                    AND fls.max_position_amount');
                })
                ->with(['coinPair', 'future_wallet'])
                ->where('future_positions.user_id', $user_id)
                ->where('future_positions.margin_mode', MarginModeEnum::CROSS->value)
                ->where('future_positions.amount', '<>', 0)
                ->where('future_positions.status', PositionStatusEnum::OPEN->value)
                ->get();

            foreach ($positions as $position) {
                $mm = PositionMath::getMaintenanceMargin(
                    price: $position->price,
                    amount: abs($position->amount),
                    maintenance_margin_rate: $position?->leverageSetting->maintenance_margin_rate ?? DefaultMarginValue::MARGIN_RATE->value,
                    maintenance_amount: $position?->leverageSetting->maintenance_amount ?? DefaultMarginValue::MARGIN_AMOUNT->value,
                    scale: $scale
                );
                $maintenance_margin = bcaddx($maintenance_margin, $mm, $scale);
            }

            $position = $positions->first();
            $margin_balance = '0';

            // Cross: Cross Margin Balance = Wallet - Sum(All Isolated Position Margin) + Cross Unrealized PnL
            $sum_isolated_margin = FuturePosition::query()
                ->where('user_id', $user_id)
                ->where('margin_mode', MarginModeEnum::ISOLATED->value)
                ->where('status', PositionStatusEnum::OPEN->value)
                ->where('amount', '<>', 0)
                ->get()
                ->reduce(function (string $carry, FuturePosition $pos) use ($scale) {
                    return bcaddx($carry, $pos->margin_balance ?: '0', $scale);
                }, '0');

            $cross_unrealized_pnl = '0';
            FuturePosition::query()
                ->with('coinPair')
                ->where('user_id', $user_id)
                ->where('margin_mode', MarginModeEnum::CROSS->value)
                ->where('status', PositionStatusEnum::OPEN->value)
                ->where('amount', '<>', 0)
                ->get()
                ->each(function (FuturePosition $pos) use (&$cross_unrealized_pnl, $scale) {
                    $cp = $pos->coinPair;
                    if (!$cp) {
                        return;
                    }
                    $mp = cache_service()->getMarkPrice($cp->id) ?: '0';
                    $pnl = PositionMath::getPnl(
                        mark_price: $mp,
                        entryPrice: $pos->price,
                        position_size: abs($pos->amount),
                        direction: $pos->order_type,
                        scale: $scale
                    );
                    $cross_unrealized_pnl = bcaddx($cross_unrealized_pnl, $pnl, $scale);
                });

            $costService = new OrderCost(authId());
            $orderCost = $costService->cost()->total_cost ?? '0';

            $wallet_balance = bcsubx($wallet_balance, $orderCost, $scale);

            $margin_balance = bcaddx(
                bcsubx($wallet_balance, $sum_isolated_margin, $scale),
                $cross_unrealized_pnl,
                $scale
            );

            $margin_ratio = PositionMath::getMarginRation(
                maintenance_margin: $maintenance_margin,
                margin_balance: $margin_balance,
                scale: $scale
            );

            return [
                'maintenance_margin' => trim_num($maintenance_margin, $scale),
                'margin_balance' => trim_num($margin_balance, $scale),
                'wallet_balance' => trim_num($wallet_balance, $scale),
                'margin_ratio' => trim_num($margin_ratio, $scale),
            ];

        }

        // Isolated: margin balance = Isolated position margin + Unrealized PnL
        $pnl = PositionMath::getPnl(
            mark_price: $mark_price,
            entryPrice: $position->price,
            position_size: abs($position->amount),
            direction: $position->order_type,
            scale: $scale
        );
        $position_margin = $position->margin_balance ?: '0';
        $margin_balance = bcaddx($position_margin, $pnl, $scale);
        $mm = PositionMath::getMaintenanceMargin(
            price: $position->price,
            amount: abs($position->amount),
            maintenance_margin_rate: $position?->leverageSetting->maintenance_margin_rate ?? DefaultMarginValue::MARGIN_RATE->value,
            maintenance_amount: $position?->leverageSetting->maintenance_amount ?? DefaultMarginValue::MARGIN_AMOUNT->value,
            scale: $scale
        );

        $maintenance_margin = bcaddx($maintenance_margin, $mm, $scale);

        $margin_ratio = PositionMath::getMarginRation(
            maintenance_margin: $maintenance_margin,
            margin_balance: $margin_balance,
            scale: $scale
        );

        return [
            'maintenance_margin' => trim_num($maintenance_margin, $scale),
            'margin_balance' => trim_num($margin_balance, $scale),
            'wallet_balance' => trim_num($wallet_balance, $scale),
            'margin_ratio' => trim_num($margin_ratio, $scale),
        ];
    }

    public function getUserAssetsSummary(string|FuturePosition $position, ?int $user_id = null): array
    {
        $user_id ??= authId();
        $response = [
            'balance' => '0.00',
            'unrealized_pnl' => '0.00',
            'trade_coin_code' => 'USDT'
        ];

        if ($position instanceof FuturePosition) {
            $targetPosition = $position;
        } else {
            // $position is a coin pair UID string
            $coinPair = CoinPairRepository::getCoinPairByUid($position);

            if (!$coinPair) {
                return $response;
            }

            $targetPosition = FuturePosition::where('future_coin_pair_id', $coinPair->id)
                ->where('user_id', $user_id)
                ->where('status', PositionStatusEnum::OPEN->value)
                ->where('amount', '<>', 0)
                ->first();
        }

        if (!$targetPosition) {
            return $response;
        }

        $coinPair = $targetPosition->coinPair;
        if (!$coinPair) {
            ResponseFacade::failed(__("Coin pair not found"), $response)->throw();
        }

        $wallet = FutureWallet::where([
                'user_id' => $user_id,
                'coin_id' => $targetPosition->trade_coin_id
            ])->first();

        $decimal = $coinPair->trade_decimal ?? 8;
        $wallet_balance = $wallet?->balance ?? '0';
        $balance = truncate_num($wallet_balance, $decimal);

        $mark_price = cache_service()->getMarkPrice($coinPair->id);

        if ($targetPosition->margin_mode == MarginModeEnum::ISOLATED) {
            $target_pnl = PositionMath::getPnl(
                mark_price: $mark_price,
                entryPrice: $targetPosition->price,
                position_size: abs($targetPosition->amount),
                direction: $targetPosition->order_type,
                scale: $decimal
            );
            $unrealized_pnl = $target_pnl;
        } else {
            $all_positions = FuturePosition::with('coinPair')
                ->where('user_id', $user_id)
                ->where('trade_coin_id', $targetPosition->trade_coin_id)
                ->where('status', PositionStatusEnum::OPEN->value)
                ->where('amount', '<>', 0)
                ->get();

            $cross_pnl = '0';
            $isolated_margin_total = '0';

            foreach ($all_positions as $pos) {
                if ($pos->margin_mode == MarginModeEnum::ISOLATED) {
                    $isolated_margin_total = bcaddx($isolated_margin_total, $pos->margin_balance, $decimal);
                    continue;
                }

                $posCoinPair = $pos->coinPair;
                if (!$posCoinPair) {
                    continue;
                }

                $posDecimal = $posCoinPair->trade_decimal ?? 8;
                $posMarkPrice = cache_service()->getMarkPrice($pos->future_coin_pair_id);

                $pnl = PositionMath::getPnl(
                    mark_price: $posMarkPrice,
                    entryPrice: $pos->price,
                    position_size: abs($pos->amount),
                    direction: $pos->order_type,
                    scale: $posDecimal
                );

                $sum_scale = max((int) $decimal, (int) $posDecimal);
                $cross_pnl = bcaddx($cross_pnl, $pnl, $sum_scale);
            }

            // $balance = bcsubx($wallet_balance, $isolated_margin_total, $decimal);
            // $balance = bcaddx($balance, $cross_pnl, $decimal);

            $unrealized_pnl = $cross_pnl;
        }

        return [
            'balance' => $balance,
            'unrealized_pnl' => $unrealized_pnl,
            'trade_coin_code' => 'USDT'
        ];
    }



    public function positionInfo(FuturePosition $position): array
    {
        $orderType = $position->order_type;
        $coinPair = $position->coinPair;

        if (!$orderType) {
            return [];
        }

        if (!$coinPair) {
            return [];
        }

        $positions = (new Collection())->add($position);
        $mark_price = cache_service()->getMarkPrice($coinPair->id);
        $maintenance_margin = $this->getMaintenanceMargin($coinPair, $positions);

        [$marginBalance, $total_isolated_balance, $cross_balance_liq]
            = $this->getMarginBalance($coinPair, $positions, $mark_price);

        $pnl = PositionMath::getPnl(
            mark_price: $mark_price,
            entryPrice: $position->price,
            position_size: abs($position->amount),
            direction: $position->order_type,
            scale: $coinPair?->trade_decimal ?: 8
        );

        if($position->margin_mode == MarginModeEnum::ISOLATED){
            $marginBalance = bcsubx($marginBalance, $pnl, $coinPair->trade_decimal ?? 8);
        }

        $margin_ratio = PositionMath::getMarginRation(
            maintenance_margin: $maintenance_margin,
            margin_balance: $marginBalance,
            scale: $coinPair?->trade_decimal ?: 8
        );

        return [
            'uid' => $position->uid,
            'price' => trim_num($position->price, $coinPair?->trade_decimal ?: 8),
            'amount' => trim_num($position->amount, $coinPair?->trade_decimal ?: 8),
            'pnl' => trim_num($pnl, $coinPair?->trade_decimal ?: 8),
            'ratio' => trim_num($margin_ratio, $coinPair?->trade_decimal ?: 8),
            'liquidation_price' => trim_num($cross_balance_liq, $coinPair?->trade_decimal ?: 8),
            'margin' => trim_num($marginBalance, $coinPair?->trade_decimal ?: 8)
        ];
    }

    public function getMyOpenPositionHistory(FuturePositionHistoryFilterRequest $request)
    {
        $user_id = authId();
        $query = FuturePositionHistory::query()
            ->where('future_positions.user_id', $user_id)
            ->select([
                'future_position_histories.future_position_id',
                'future_position_histories.price',
                'future_position_histories.amount',
                'future_position_histories.status',
                'future_position_histories.created_at',
                'future_coin_pairs.code',
                'future_coin_pairs.trade_decimal',
            ]);

        $query = $query->join(
            'future_positions',
            'future_positions.id',
            '=',
            "future_position_histories.future_position_id"
        );

        $query = $query->join(
            'future_coin_pairs',
            'future_coin_pairs.id',
            '=',
            "future_positions.future_coin_pair_id"
        );

        // Symbol Filter
        if ($request->filled('symbol')) {
            $query = $query->where('future_coin_pairs.code', $request->symbol);
        }

        // Time Filter
        if ($request->filled('time')) {
            $time_to = now();
            $time_from = $time_to->copy()->subDays($request->time);

            $query = $query->whereBetween("future_position_histories.created_at", [$time_from->toDateTimeString(), $time_to->toDateTimeString()]);
        }

        if ($request->filled('time_from')) {
            $time_to = Carbon::parse($request->input('time_to'));
            $time_from = Carbon::parse($request->input('time_from'));

            $query = $query->whereBetween("future_position_histories.created_at", [$time_from->toDateTimeString(), $time_to->toDateTimeString()]);
        }

        $history = $query->orderByDesc('future_position_histories.created_at')
            ->paginate($request->limit ?? 20);

        $items = $history->getCollection()->map(function ($row) {
            return [
                'symbol' => $row?->code ?? 'N/A',
                'price' => trim_num($row->price, $row?->trade_decimal ?: 8),
                'amount' => trim_num($row->amount, $row?->trade_decimal ?: 8),
                'status' => $row->status instanceof PositionStatusEnum ? $row->status->value : ($row->status ?: 0),
                'created_at' => $row->created_at,
            ];
        })->values();

        $history->setCollection($items);

        return $history;
    }

    public function updateTpSl(PositionTpSlUpdateRequest $request, ?int $user_id = null)
    {
        $user_id ??= authId();

        return DB::transaction(function () use ($user_id, $request) {
            $position = PositionRepository::getPositionBuilderByUid($request->position_uid)
                ->getLeverageSetting()
                ->select('future_positions.*', 'fls.min_position_amount','fls.max_position_amount','fls.max_leverage','fls.maintenance_margin_rate','fls.maintenance_amount')
                ->where('future_positions.user_id', $user_id)
                ->where('future_positions.amount', '<>', 0)
                ->where('future_positions.status', PositionStatusEnum::OPEN->value)
                ->lockForUpdate()->first();

            if (!$position) {
                ResponseFacade::failed(__("Position not found"))->throw();
            }

            $updateData = [];
            if ($request->filled('tp_price') && $request->tp_price) {
                $updateData['tp_price'] = $request->tp_price;
            }

            if ($request->filled('sl_price') && $request->sl_price) {
                $updateData['sl_price'] = $request->sl_price;
            }

            if (!$updateData) {
                ResponseFacade::failed(__("Position tp/sl are not valid"))->throw();
            }

            $updated = $position->update($updateData);

            if (!$updated) {
                ResponseFacade::failed(__("Position tp/sl update failed"))->throw();
            }

            rescue(function () use ($user_id, $position) {
                $position->load(['coinPair']);
                $data = $this->getPositionInfo($position);
                PositionBroadcastEvent::dispatch($user_id, $data);
                $this->broadcastUserTradingState($user_id, $position->coinPair->uid);
            });

            return success(__("Position tp/sl updated successfully"));
        });
    }

    public function cancelTpSl(PositionTpSlCancelRequest $request, ?int $user_id = null)
    {
        $user_id ??= authId();

        return DB::transaction(function () use ($user_id, $request) {
            $position = PositionRepository::getPositionBuilderByUid($request->position_uid)
                ->getLeverageSetting()
                ->select('future_positions.*', 'fls.min_position_amount','fls.max_position_amount','fls.max_leverage','fls.maintenance_margin_rate','fls.maintenance_amount')
                ->where('future_positions.user_id', $user_id)
                ->where('future_positions.amount', '<>', 0)
                ->where('future_positions.status', PositionStatusEnum::OPEN->value)
                ->lockForUpdate()->first();

            if (!$position) {
                ResponseFacade::failed(__("Position not found"))->throw();
            }

            $updateData = [];
            $tpslType = TpSlType::tryFrom($request->type) ?? null;
            if ($tpslType == TpSlType::TAKE_PROFIT) {
                $updateData['tp_price'] = 0;
            }

            if ($tpslType == TpSlType::STOP_LOSS) {
                $updateData['sl_price'] = 0;
            }

            if (!$updateData) {
                ResponseFacade::failed(__("Position tp/sl are not valid"))->throw();
            }

            $updated = $position->update($updateData);

            if (!$updated) {
                ResponseFacade::failed(__("Position tp/sl cancel failed"))->throw();
            }

            rescue(function () use ($user_id, $position) {
                $position->load(['coinPair']);
                $data = $this->getPositionInfo($position);
                PositionBroadcastEvent::dispatch($user_id, $data);
                $this->broadcastUserTradingState($user_id, $position->coinPair->uid);
            });

            return success(__("Position tp/sl canceled successfully"));
        });
    }

    public function totalPnlCalculation(Collection $positions): int|string
    {
        $total_pnl = 0;

        foreach ($positions as $position) {
            if (is_array($position)) {
                $position = (object) $position;
                $coinPair = FutureCoinPair::where('code', $position->code)->first();
                $position->order_type = OrderType::tryFrom($position->order_type) ?? OrderType::BUY;
            } else {
                $coinPair = $position->coinPair;
            }

            if (!$coinPair) {
                continue;
            }

            $mark_price = cache_service()->getMarkPrice($coinPair->id);

            $pnl = PositionMath::getPnl(
                mark_price: $mark_price,
                entryPrice: $position->price,
                position_size: abs($position->amount),
                direction: $position->order_type,
                scale: $coinPair?->trade_decimal ?: 8
            );

            $total_pnl = bcaddx($total_pnl, $pnl, $coinPair?->trade_decimal ?: 8);
        }

        return $total_pnl;
    }

    public function broadcastUserTradingState(int $user_id, string $coin_pair_uid)
    {
        rescue(function () use ($user_id, $coin_pair_uid) {
            $marginSummary = $this->getUserMarginSummary($coin_pair_uid, $user_id);
            PrivateMarginSummeryBroadcastEvent::dispatch($user_id, $coin_pair_uid, $marginSummary);

            $assetSummary = $this->getUserAssetsSummary($coin_pair_uid, $user_id);
            PrivateUserAssetMarginBroadcastEvent::dispatch($user_id, $coin_pair_uid, $assetSummary);

            $orderSummary = app(OrderSummaryService::class)
                ->getOpenOrdersAndPositionsSummary($coin_pair_uid, $user_id);
            PrivateOrderSummaryBroadcastEvent::dispatch($user_id, $coin_pair_uid, $orderSummary);

            $this->broadcastUserWalletDetails($user_id);
        });
    }

    private function broadcastUserWalletDetails(int $user_id): void
    {
        rescue(function () use ($user_id) {
            $walletService = app(WalletService::class);
            $walletDetails = $walletService->userWalletDetails($user_id)['data'] ?? [];
            PrivateUserWalletBroadcastEvent::dispatch($user_id, $walletDetails);
        });
    }
}
