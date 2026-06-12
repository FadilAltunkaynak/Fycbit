<?php

namespace Modules\FutureTrade\Services\OrderService;

use App\Facades\ResponseFacade;
use App\User;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\Jobs\OrderCancelJob;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\DataObject\FutureTradeData;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\TpSlType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Entities\FutureUserSetting;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Events\OrderBookBroadcastEvent;
use Modules\FutureTrade\Events\TodayChangeBroadcastEvent;
use Modules\FutureTrade\Events\TradeBroadcastEvent;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderCancelRequest;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderHistoryFilterRequest;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderRequest;
use Modules\FutureTrade\Http\Requests\Api\MyOrderListRequest;
use Modules\FutureTrade\Http\Requests\Api\OrderBookRequest;
use Modules\FutureTrade\Jobs\FutureOpenPositionJob;
use Modules\FutureTrade\Jobs\FutureStopLimitOrderJob;
use Modules\FutureTrade\Jobs\OrderCreationJob;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;
use Modules\FutureTrade\Repositories\OrderRepository\OrderRepository;
use Modules\FutureTrade\Services\CostService\CostService;
use Modules\FutureTrade\Services\NotificationServices\NotificationService;
use Modules\FutureTrade\Services\OrderService\Traits\OrderCacheAble;
use Modules\FutureTrade\Services\OrderService\Traits\Validation;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;
use Modules\FutureTrade\Services\TradeServices\TradingViewChartService;

class OrderService
{
    use OrderCacheAble, Validation;

    public OrderRepository $repository;

    public function __construct(IOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Future Order Process
     *
     * @return array{data: mixed, message: string, success: bool}
     */
    public function orderProcess(FutureOrderRequest $request)
    {
        // Check Coin Pair
        /** @var FutureCoinPair $coin_pair */
        $coin_pair = $this->validateCoinPair($request->coin_pair_uid);

        $margin_mode = FutureUserSetting::where([
            'user_id' => authId(),
            'coin_pair_id' => $coin_pair->id
        ])->value('margin_mode');

        if (!$margin_mode) {
            ResponseFacade::failed(__('Margin mode not found'))->safeThrow();
        }

        $request->merge(['margin_mode' => $margin_mode->value]);

        $market_price = app('future-cache')->getMarketPrice($coin_pair->id) ?? 0;
        $market_price = trim_num((string) $market_price ?: 0, $coin_pair->trade_decimal ?: 8) ?: '0';

        $markPrice = app('future-cache')->getMarkPrice($coin_pair->id) ?? 0;
        $markPrice = trim_num((string) $markPrice, $coin_pair->trade_decimal ?: 8) ?: '0';

        $indexPrice = app('future-cache')->getIndexPrice($coin_pair->id) ?? 0;
        $indexPrice = trim_num((string) $indexPrice, $coin_pair->trade_decimal ?: 8) ?: '0';

        $request->price ??= $market_price;
        $orderData = FutureOrderData::fromOrderRequest(
            $request,
            $coin_pair
        );

        if ($orderData->order_method === OrderMethod::MARKET) {
            $oppositeOrderType = $orderData->order_type === OrderType::BUY
                ? OrderType::SELL
                : OrderType::BUY;

            $hasOppositeOrder = $this->repository
                ->getOrderBuilder($coin_pair->id, $oppositeOrderType)
                ->pendingOrder()
                ->where('order_method', OrderMethod::LIMIT->value)
                ->where('stop_price', 0)
                ->exists();

            if (! $hasOppositeOrder) {
                ResponseFacade::failed(__('No matching orders available.'))->throw();
            }
        }

        $costService = new CostService(authId());
        $total_cost = $costService->getCostByNewOrder($orderData);

        // check Max Order Limit
        if ($orderData->order_method !== OrderMethod::MARKET) {
            $this->maxOpenOrderValidation($coin_pair);
        }

        // Check User Margin
        $this->validateUserMargin($coin_pair, $request->margin_mode);

        // Check Amount
        $this->checkMinAndMaxAmount($coin_pair, $request->amount);

        // Take Profit and Stop Loss Validation
        $this->takeProfitAndStopLossValidation(
            orderType: $orderData->order_type,
            price: $request->price,
            take_profit_price: $request?->take_profit_price ?? 0,
            stop_loss_price: $request?->stop_loss_price ?? 0
        );

        // User Wallet Balance Validation
        $this->validateFutureWalletBalance($coin_pair->tradeCoin, $total_cost);

        $orderData->setProperty('pending_amount', $orderData->amount);
        $orderData->setProperty('market_price', $market_price);
        $orderData->setProperty('mark_price', $markPrice);
        $orderData->setProperty('index_price', $indexPrice);

        $validationResponse = match (OrderMethod::from($request->order_method)) {
            OrderMethod::MARKET => $this->checkMarketSlippage($orderData->order_type, $coin_pair->slippage_percent, $request->price),
            OrderMethod::LIMIT => $this->checkLimitValidation($coin_pair, $market_price, $request->price),
            OrderMethod::STOP_LIMIT => $this->checkStopLimitValidation($orderData),
        };

        if ($orderData->is_reduce) {
            $this->reduceOnlyValidation($orderData);
        }

        OrderCreationJob::dispatch($orderData)->onQueue('future-trade');

        return $orderData->order_type == OrderType::BUY
            ? success(__('Buy order placed successfully'))
            : success(__('Sell order placed successfully'));
    }

    /**
     * Order Job Process
     */
    public function orderJobProcess(FutureOrderData $order): void
    {
        $this->repository->setModel($order->order_type);
        // Check Coin Pair
        /** @var FutureCoinPair $coin_pair */
        $coin_pair = $this->validateCoinPair($order->coin_pair_uid);
        $trade_coin = $coin_pair->tradeCoin;
        $base_coin = $coin_pair->baseCoin;
        // $this->validateFutureWalletBalance($trade_coin, $order->total_price);

        if ($order->process_stop_limit) {
            goto PASS_VALIDATION;
        }
        // Check User Margin
        $this->validateUserMargin($coin_pair, $order->margin_mode->value, $order->user_id);

        PASS_VALIDATION:
        /** @var FutureBuy|FutureSell $new_order */
        $new_order = $this->repository->insertOrder($order);

        if (!$new_order) {
            errorLogger('87647: order not created on db');
            ResponseFacade::failed('Order Failed')->safeThrow();
        }

        if ($new_order->order_method == OrderMethod::STOP_LIMIT) {
            return;
        }

        $new_order->coin_pair = $coin_pair;
        $new_order->base_coin = $base_coin;
        $new_order->trade_coin = $trade_coin;

        $this->matchEngine($new_order);

        if ($new_order->order_method == OrderMethod::MARKET) {
            $new_order->update(['status' => OrderStatusEnum::COMPLETE->value]);
        }else{
            $new_order->refresh();
            if ($new_order->status === OrderStatusEnum::PROCESSING) {
                $new_order->update([
                    'status' => OrderStatusEnum::PENDING->value
                ]);
            }

            rescue(function () use ($new_order, $coin_pair) {
                OrderBookBroadcastEvent::dispatch($coin_pair->uid, $new_order);
            });
        }


        app(FuturePositionService::class)->broadcastUserTradingState($order->user_id, $order->coin_pair_uid);
    }

    public function matchEngine(FutureBuy|FutureSell $order)
    {
        $this->repository->setModel($order->order_type);
        $pending_amount = $this->repository->getPendingAmount($order);
        $user = User::find($order->user_id);
        if (!$user) {
            ResponseFacade::failed(__('User not found'))->safeThrow();
        }

        if ($order->stop_price > 0) {
            NotificationService::stopLimitOrderProcess($user->id);
        } else {
            NotificationService::orderProcess($user->id);
        }

        /** @var IOrderRepository $repository */
        $repository = app('future-order-repository');

        array_map(
            function ($orderType) use ($order, $repository) {

                if ($orderType == $order->order_type) {
                    return;
                }

                $orders = $repository->getOrdersRecursive(
                    coinPair: $order->future_coin_pair_id,
                    orderType: $order->order_type == OrderType::BUY ? OrderType::SELL : OrderType::BUY,
                    orderMethod: $order->order_method,
                    price: $order->price
                );

                while ($orders) {
                    $matchOrders = $orders->current();

                    foreach ($matchOrders as $matchOrder) {
                        $this->handelMatchedOrder(
                            order: $order,
                            matchOrder: $matchOrder
                        );
                    }

                    if (is_null($orders->next())) {
                        break;
                    }
                }

                if ($order->stop_price > 0) {
                    $order->update(['stop_price' => 0]);
                }
            },
            [OrderType::BUY, OrderType::SELL]
        );
    }

    public function handelMatchedOrder(FutureBuy|FutureSell $order, $matchOrder)
    {
        $stopLimitOrder = (bool) $order->stop_price;
        if ($matchOrder->pending_amount <= 0) {
            errorLogger('99698: Matched Order pending amount not valid');
            return;
        }

        $total = 'visualNumberFormat(TRUNCATE(sum((amount - processed_amount) * price), 8)) as total';
        $buyAbleAmount = 'amount - processed_amount as buyAbleAmount';
        $sellAbleAmount = 'amount - processed_amount as sellAbleAmount';

        $buy = $sell = null;
        DB::beginTransaction();
        try {
            debugLogger('order lock from order matching engine');
            if ($order->order_type == OrderType::BUY) {
                $buy = FutureBuy::selectRaw("$total, $buyAbleAmount, future_buys.*")
                    ->lockForUpdate()
                    ->where('id', $order->id)
                    ->whereIn('status', [OrderStatusEnum::PENDING->value, OrderStatusEnum::PROCESSING->value])
                    ->first();
                $sell = FutureSell::selectRaw("$total, $sellAbleAmount, future_sells.*")
                    ->lockForUpdate()
                    ->where('id', $matchOrder->id)
                    ->whereStatus(OrderStatusEnum::PENDING->value)
                    ->first();
            } else {
                $buy = FutureBuy::selectRaw("$total, $buyAbleAmount, future_buys.*")
                    ->lockForUpdate()
                    ->where('id', $matchOrder->id)
                    ->whereStatus(OrderStatusEnum::PENDING->value)
                    ->first();
                $sell = FutureSell::selectRaw("$total, $sellAbleAmount, future_sells.*")
                    ->lockForUpdate()
                    ->where('id', $order->id)
                    ->whereIn('status', [OrderStatusEnum::PENDING->value, OrderStatusEnum::PROCESSING->value])
                    ->first();
            }

            if (!$buy->id || !$sell->id) {
                errorLogger('99699: Buy or Sell not found');
                DB::rollBack();
                return;
            }

            $buy->coin_pair = $order->coin_pair;
            $sell->coin_pair = $order->coin_pair;
            $matchOrder->coin_pair = $order->coin_pair;

            $sellAbleAmount = $sell->sellAbleAmount;
            $buyAbleAmount = $buy->buyAbleAmount;

            if (
                bccompx($buyAbleAmount, '0') == 0 ||
                bccompx($sellAbleAmount, '0') == 0
            ) {
                errorLogger('99679: Buy or Sell amount not valid');
                DB::rollBack();
                return;
            }

            $amountToBeProcessed = 0;
            if (bccompx($buyAbleAmount, $sellAbleAmount) != 1) {
                $amountToBeProcessed = $buyAbleAmount;
            } else {
                $amountToBeProcessed = $sellAbleAmount;
            }

            [$maker_fees, $taker_fees] = self::getTakerAndMakerFees(
                coinPair: $order->coin_pair,
                price: $matchOrder->price,
                processed_amount: $amountToBeProcessed
            );

            $this->validateProcessAndPendingAmount($buy, $sell, $amountToBeProcessed);

            $tradeData = FutureTradeData::fromOrderPrecess(
                order: $order,
                matchOrder: $matchOrder,
                amountToBeProcessed: $amountToBeProcessed,
                maker_fees: $maker_fees,
                taker_fees: $taker_fees
            );

            /** @var ?FutureTrade $tradeData */
            $tradeData = rescue(fn() => $tradeData->create(), null);
            if (!$tradeData) {
                errorLogger('Trade record not created');
                DB::rollBack();
                return;
            }

            $equal = bccompx($buyAbleAmount, $sellAbleAmount) == 0;
            $less = bccompx($buyAbleAmount, $sellAbleAmount) == -1;
            $more = bccompx($buyAbleAmount, $sellAbleAmount) == 1;

            $buyOrderCompletedUpdateStatus = match (true) {
                $less, $equal => ['status' => OrderStatusEnum::COMPLETE->value],
                default => []
            };

            $sellOrderCompletedUpdateStatus = match (true) {
                $more, $equal => ['status' => OrderStatusEnum::COMPLETE->value],
                default => []
            };

        
            $buy->increment('processed_amount', $amountToBeProcessed, $buyOrderCompletedUpdateStatus);
            $buy->decrement('pending_amount', $amountToBeProcessed, ['stop_price' => 0]);
            $sell->increment('processed_amount', $amountToBeProcessed, $sellOrderCompletedUpdateStatus);
            $sell->decrement('pending_amount', $amountToBeProcessed, ['stop_price' => 0]);
            $this->decrementFeesFromOrder($tradeData, $buy, $sell);
        } catch (\Throwable $th) {
            debugLogger("Order: handel matched order failed process.");
            debugLogger($th->getMessage());
            DB::rollBack();
            return;
        }
        DB::commit();
        // set market price on cache
        cache_service()->setMarketPrice($order->coin_pair->id, $tradeData->price);

        if (!$stopLimitOrder) {
            FutureStopLimitOrderJob::dispatch($tradeData->id)->onQueue('future-stop-limit');
        }

        rescue(function () use ($order, $tradeData, $matchOrder) {
            OrderBookBroadcastEvent::dispatch($order->coin_pair->uid, $matchOrder);
            TodayChangeBroadcastEvent::dispatch($order->coin_pair->id);
            TradeBroadcastEvent::dispatch($order->coin_pair->uid, $order->coin_pair->code, $tradeData);
            // PrivateTradeBroadcastEvent::dispatch($buy->user_id, $order->coin_pair->uid, $tradeData->toArray());
            // PrivateTradeBroadcastEvent::dispatch($sell->user_id, $order->coin_pair->uid, $tradeData->toArray());
        });

        // update chart candle
        $chartService = new TradingViewChartService();
        $tradeData->coinPair = $order->coin_pair;
        $chartService->updateCandleData($tradeData);

        FutureOpenPositionJob::dispatch(
            $tradeData->id,
            $tradeData->buyer_id,
            $tradeData->seller_id,
            $tradeData->future_coin_pair_id
        )->onQueue('future-trade');

        if ($buy->pending_amount == 0) {
            NotificationService::orderFilled($buy->user_id);
        } else if ($sell->pending_amount == 0) {
            NotificationService::orderFilled($sell->user_id);
        }

        return;
    }

    private function decrementFeesFromOrder(FutureTrade $trade, FutureBuy &$buy, FutureSell &$sell)
    {
        if(!$buy->is_bot){
            $buy->futureWallet();

            if(!$buy->wallet instanceof FutureWallet){
                DB::rollBack();
                ResponseFacade::failed(__('Wallet Not Found'))->safeThrow();
            }

            if ($trade->order_type == OrderType::BUY) {
                $buy->wallet->decrement('balance', $trade->taker_fees ?: 0);
            } else {
                $buy->wallet->decrement('balance', $trade->maker_fees ?: 0);
            }
        }
        
        if(!$sell->is_bot){
            $sell->futureWallet();

            if(!$sell->wallet instanceof FutureWallet){
                DB::rollBack();
                ResponseFacade::failed(__('Wallet Not Found'))->safeThrow();
            }

            if ($trade->order_type == OrderType::BUY) {
                $sell->wallet->decrement('balance', $trade->maker_fees ?: 0);
            } else {
                $sell->wallet->decrement('balance', $trade->taker_fees ?: 0);
            }
        }
    }

    /**
     * Summary of getTakerAndMakerFees
     *
     * @return array{maker_fees: int|string[], taker_fees: int|string[]}
     */
    public static function getTakerAndMakerFees(
        FutureCoinPair $coinPair,
        float|string $price,
        float|string $processed_amount
    ): array {
        $makerFeePercent = $coinPair->maker_fees_percent;
        $takerFeePercent = $coinPair->taker_fees_percent;
        $totalCost = bcmulx($processed_amount, $price, $coinPair->trade_decimal);

        // 0 => maker
        // 1 => taker
        $fees = [0, 0];

        // maker fees
        if ($makerFeePercent > 0) {
            $percentValue = bcdivx($makerFeePercent, 100);
            $fees[0] = bcmulx($totalCost, $percentValue, $coinPair->trade_decimal);
        }

        // taker fees
        if ($takerFeePercent > 0) {
            $percentValue = bcdivx($takerFeePercent, 100);
            $fees[1] = bcmulx($totalCost, $percentValue, $coinPair->trade_decimal);
        }

        return $fees;
    }

    public function myOpenOrderList(MyOrderListRequest $request)
    {
        $paginator = $this->repository->orderHistoryFilter($request, OrderStatusEnum::PENDING);
        $items = $paginator->getCollection()->map(function ($order) {
            return [
                'uid' => $order->uid,
                'symbol' => $order->code,
                'type' => $order->order_method instanceof OrderMethod ? $order->order_method->value : ($order->order_method ?: 0),
                'created_at' => $order->created_at,
                'side' => $order->side,
                'price' => trim_num($order->price, $order->trade_decimal ?: 8),
                'amount' => trim_num($order->amount, $order->base_decimal ?: 8),
                'filed' => trim_num($order->processed_amount, $order->base_decimal ?: 8),
                'base_coin_code' => $order->base_coin_code ?: 'N/A',
                'trade_coin_code' => $order->trade_coin_code ?: 'N/A',
                'reduce_only' => $order->is_reduce ? 1 : 0,
                'trigger_conditions' => '-',
                'tp_price' => trim_num($order->tp_price, $order->trade_decimal ?: 8),
                'sl_price' => trim_num($order->sl_price, $order->trade_decimal ?: 8),
            ];
        })->values();

        $paginator->setCollection($items);

        return $paginator;
    }

    public function tpSl_Order(FuturePosition $position, TpSlType $tpslType, string|float $mark_price)
    {
        $orderType = $position->order_type == OrderType::BUY ? OrderType::SELL : OrderType::BUY;

        $pending_order = $this->repository->getOrderBuilder($position->future_coin_pair_id, $orderType)
            ->pendingOrder()
            ->where('is_reduce', 1)
            ->where('user_id', $position->user_id)
            ->first();

        if ($pending_order) {
            debugLogger('tpSl_Order place: already reduce only order exist');
            return;
        }

        $orderData = FutureOrderData::fromTpSlOrderMaker($position, $orderType);
        $orderData->setProperty('tpsl_type', $tpslType);
        $orderData->setProperty('mark_price', $mark_price);

        DB::beginTransaction();
        $newOrder = rescue(fn() => ($this->repository->create($orderData->toArray())));
        if (!$newOrder) {
            debugLogger('tpSl_Order place: Order Insert Failed');
            DB::rollBack();
            return;
        }

        $newOrder->refresh();
        $positionUpdate = $position->update([
            'tp_price' => 0,
            'sl_price' => 0
        ]);

        if (!$positionUpdate) {
            debugLogger('tpSl_Order place: Position Update Failed');
            DB::rollBack();
            return;
        }

        DB::commit();
        $newOrder->coin_pair = $position->coin_pair;
        $this->matchEngine($newOrder);
        NotificationService::positionClosedOrder($position->user_id);
    }

    public function orderHistoryFilter(FutureOrderHistoryFilterRequest $request)
    {
        $paginator = $this->repository->orderHistoryFilter($request);
        $items = $paginator->getCollection()->map(function ($order) {
            return [
                'uid' => $order->uid,
                'symbol' => $order->code,
                'type' => $order->order_method instanceof OrderMethod ? $order->order_method->value : ($order->order_method ?: 0),
                'created_at' => $order->created_at,
                'side' => $order->side,
                'price' => trim_num($order->price, $order->trade_decimal ?: 8),
                'executed' => trim_num($order->processed_amount, $order->trade_decimal ?: 8),
                'amount' => trim_num($order->amount, $order->trade_decimal ?: 8),
                'base_coin_code' => $order->base_coin_code,
                'trade_coin_code' => $order->trade_coin_code,
                'reduce_only' => $order->is_reduce ? 1 : 0,
                'trigger_conditions' => '-',
                'tp_price' => trim_num($order->tp_price, $order->trade_decimal ?: 8),
                'sl_price' => trim_num($order->sl_price, $order->trade_decimal ?: 8),
                'status' => $order->status
            ];
        })->values();

        $paginator->setCollection($items);

        return $paginator;
    }

    public function orderCancelAddQueue(FutureOrderCancelRequest $request, ?int $user_id = null)
    {
        $user_id ??= authId();
        $orderType = OrderType::from($request->order_type);

        $key = "order-cancel-for-$user_id-$request->order_type-$request->order_uid";
        if (cache()->has($key)) {
            ResponseFacade::failed(__('This order cancel process on queue'))->safeThrow();
        }

        $order = $this->repository->getOrderByUid(
            orderUid: $request->order_uid,
            orderType: $orderType
        )->where('user_id', $user_id)->pendingOrder()->first();

        if (!$order) {
            ResponseFacade::failed(__('Order not found for cancel'))->safeThrow();
        }

        cache()->put($key, 1, 10);

        $orderId = $order->id;
        $value = $orderType->value;
        OrderCancelJob::dispatch(
            $user_id,
            $orderId,
            $value
        )->onQueue('future-trade');
    }

    public function orderCancelProcess(int $user_id, int $order_id, int $order_type)
    {
        DB::transaction(function () use ($user_id, $order_id, $order_type) {
            $orderType = OrderType::from($order_type);

            $order = $this->repository->getOrderById(
                orderId: $order_id,
                orderType: $orderType
            )
                ->where('user_id', $user_id)
                ->pendingOrder()
                ->with('coinPair')
                ->lockForUpdate()->first();

            if (!$order) {
                debugLogger(__('Order not found for cancel'));
                return;
            }

            $key = "order-cancel-for-$user_id-$order_type-$order->uid";

            $is_canceled = $order->update([
                'status' => OrderStatusEnum::CANCEL->value
            ]);

            if (!$is_canceled) {
                debugLogger(__('Order not canceled'));

                return;
            }

            NotificationService::orderCancel(
                $user_id,
                $order->pending_amount,
                $order?->coinPair?->base_coin_code ?: '',
                $orderType
            );

            try {
                $order->loadMissing('coinPair');
                $order->coin_pair = $order->coinPair;
                OrderBookBroadcastEvent::dispatch($order?->coinPair?->uid ?? 'null', $order);
            } catch (\Throwable $e) {
                debugLogger(__('Orderbook broadcast failed: ') . $e->getMessage());
            }

            if (cache()->has($key)) {
                cache()->forget($key);
            }

            app(FuturePositionService::class)->broadcastUserTradingState($user_id, $order->coinPair?->uid ?? 'null');
        });
    }

    public function getOrderbook(OrderBookRequest $request)
    {
        $coinPair = CoinPairRepository::getCoinPairByUid($request->coin_pair_uid);

        if (!$coinPair) {
            ResponseFacade::failed(_('Coin pair not found'))->throw();
        }

        $buyOrders = $this->repository->getOrders(
            coinPair: $coinPair->id,
            orderType: OrderType::BUY,
            orderMethod: OrderMethod::LIMIT,
            paginate: false
        )->select('price', DB::raw('SUM(pending_amount) as pending_amount'))
            ->groupBy('price')
            ->limit($request->limit ?: 20)->get();

        $sellOrders = $this->repository->getOrders(
            coinPair: $coinPair->id,
            orderType: OrderType::SELL,
            orderMethod: OrderMethod::LIMIT,
            paginate: false
        )->select('price', DB::raw('SUM(pending_amount) as pending_amount'))
            ->groupBy('price')
            ->limit($request->limit ?: 20)->get();

        return ['buy' => $buyOrders, 'sell' => $sellOrders];
    }
}
