<?php

namespace Modules\FutureTrade\Services\BotService;

use App\Dtos\BotCoinPairDto;
use App\Facades\ResponseFacade;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\DataObject\FutureTradeData;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Events\OrderBookBroadcastEvent;
use Modules\FutureTrade\Events\TodayChangeBroadcastEvent;
use Modules\FutureTrade\Events\TradeBroadcastEvent;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;
use Modules\FutureTrade\Repositories\OrderRepository\OrderRepository;
use Modules\FutureTrade\Services\ExternalPriceServices\FutureTickerService;
use Modules\FutureTrade\Services\OrderService\OrderService;
use Modules\FutureTrade\Services\OrderService\Traits\Validation;
use Modules\FutureTrade\Services\TradeServices\TradingViewChartService;

class BotService
{
    use Validation;
    public OrderRepository $repository;
    public OrderService $orderService;
    private FutureCoinPair|BotCoinPairDto $coinPair;

    public function __construct(IOrderRepository $repository)
    {
        $this->repository = $repository;
        $this->orderService = app(OrderService::class);
    }

    public function orderJobProcess(FutureOrderData $order): void
    {
        $this->repository->setModel($order->order_type);
        // Check Coin Pair
        /** @var FutureCoinPair $coin_pair */
        $coin_pair = $this->validateCoinPair($order->coin_pair_uid);
        $trade_coin = $coin_pair->tradeCoin;
        $base_coin = $coin_pair->baseCoin;

        /** @var FutureBuy|FutureSell $new_order */
        $new_order = $this->repository->insertOrder($order);

        if (!$new_order) {
            errorLogger('87647: order not created on db');
            ResponseFacade::failed('Order Failed')->safeThrow();
        }

        $new_order->coin_pair = $coin_pair;
        $new_order->base_coin = $base_coin;
        $new_order->trade_coin = $trade_coin;

        $this->matchEngine($new_order);

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

    public function getAllActiveCoinPairs(): Collection
    {
        return FutureCoinPair::query()
            ->statusActive()
            ->with('botSetting')
            ->get();
    }

    /**
     * Create buy/sell bot orders for all active future pairs using futures market price provider fallback.
     *
     * @return array{total_pairs:int,processed_pairs:int,failed_pairs:int,buy_orders:int,sell_orders:int}
     */
    public function createOrdersForAllActivePairs(int $user_id, ?float $amount = null, ?string $provider = null): array
    {
        $pairs = $this->getAllActiveCoinPairs();
        $tickerService = app(FutureTickerService::class); //app(FutureMarketTickerService::class);

        $processedPairs = 0;
        $failedPairs = 0;
        $buyOrders = 0;
        $sellOrders = 0;

        foreach ($pairs as $pair) {
            try {
                $this->coinPair = $pair;
                $botSetting = $pair->botSetting;
                if ((int) ($botSetting?->status ?? 0) !== 1) {
                    continue;
                }

                $cacheKey = 'future_bot_order_place_time_for_coin_pair_' . $pair->id;
                $orderInterval = (int) ($botSetting?->order_interval ?? 1);
                $lastPlacedAt = Cache::get($cacheKey);
                if ($lastPlacedAt) {
                    $diffInSeconds = Carbon::parse($lastPlacedAt)->diffInSeconds(now());
                    if ($diffInSeconds < $orderInterval) {
                        continue;
                    }
                }

                $symbol = $pair->code ?: ($pair->base_coin_code . $pair->trade_coin_code);
                $prices = $this->resolveBotSidePrices(
                    pair: $pair,
                    symbol: $symbol,
                    tickerService: $tickerService,
                    provider: $provider
                );
                $buyPrice = (float) ($prices['buy'] ?? 0);
                $sellPrice = (float) ($prices['sell'] ?? 0);

                if ($buyPrice <= 0 || $sellPrice <= 0) {
                    $failedPairs++;
                    continue;
                }

                $buyOrderAmount = ($amount !== null && $amount > 0)
                    ? $amount
                    : $this->generateCustomBotAmount($pair);
                $sellOrderAmount = ($amount !== null && $amount > 0)
                    ? $amount
                    : $this->generateCustomBotAmount($pair);

                $rand = rand(1, 8);
                if($rand <= 4 && ($prices['api_price_for'] ?? 0)){
                    if($prices['api_price_for'] == 1){
                        $buyOrderAmount = $this->getBestSellPrice($buyPrice);
                    }else{
                        $sellOrderAmount = $this->getBestBuyPrice($sellPrice);
                    }
                }

                if ($buyOrderAmount <= 0 || $sellOrderAmount <= 0) {
                    $failedPairs++;
                    continue;
                }

                $buyData = $this->makeBotOrderData(
                    coinPair: $pair,
                    user_id: $user_id,
                    orderType: OrderType::BUY,
                    marginMode: MarginModeEnum::CROSS,
                    leverage: 1,
                    price: $buyPrice,
                    amount: $buyOrderAmount
                );

                $sellData = $this->makeBotOrderData(
                    coinPair: $pair,
                    user_id: $user_id,
                    orderType: OrderType::SELL,
                    marginMode: MarginModeEnum::CROSS,
                    leverage: 1,
                    price: $sellPrice,
                    amount: $sellOrderAmount
                );

                async(fn() => $this->orderJobProcess($buyData));
                $buyOrders++;

                async(fn() => $this->orderJobProcess($sellData));
                $sellOrders++;

                $processedPairs++;
                Cache::put($cacheKey, now()->toDateTimeString(), 1000);
            } catch (\Throwable $th) {
                $failedPairs++;
                storeException('futureBotOrderProcess', $th->getMessage());
            }
        }

        return [
            'total_pairs' => $pairs->count(),
            'processed_pairs' => $processedPairs,
            'failed_pairs' => $failedPairs,
            'buy_orders' => $buyOrders,
            'sell_orders' => $sellOrders,
        ];
    }

    private function resolveBotSidePrices(
        FutureCoinPair $pair,
        string $symbol,
        FutureTickerService $tickerService,
        ?string $provider = null
    ): array {
        $random = random_int(1, 10);
        $localPrice = $this->resolveLocalBotPrice($pair);

        // 20% chance: API price goes to one side (buy/sell) randomly, other side gets local price
        if ($random <= 10) {
            $ticker = $tickerService->getPrice($symbol, $provider);
            $apiPrice = (float) ($ticker['price'] ?? 0);
            $apiPrice = $this->applyBotPriceRange($pair, $apiPrice);

            if ($apiPrice <= 0) {
                $apiPrice = $localPrice;
            }

            if (random_int(0, 1) === 1) {
                return [
                    'buy' => $apiPrice,
                    'sell' => $localPrice,
                    'api_price_for' => 1,
                ];
            }

            return [
                'buy' => $localPrice,
                'sell' => $apiPrice,
                'api_price_for' => 2,
            ];
        }

        // 80% chance: both sides local-generated price
        return [
            'buy' => $localPrice,
            'sell' => $this->resolveLocalBotPrice($pair),
        ];
    }

    private function resolveLocalBotPrice(FutureCoinPair $pair): float
    {
        $marketPrice = (float) (cache_service()->getMarketPrice($pair->id) ?? 0);

        if ($marketPrice <= 0) {
            return 0;
        }

        $percent = $this->getBotPricePercent($marketPrice); // very small % based on price
        $direction = random_int(0, 1) === 1 ? 1 : -1;
        $delta = $marketPrice * $percent;
        $tick = $this->getBotPriceTick($marketPrice);
        $delta = $this->roundToTick($delta, $tick);
        $adjusted = $marketPrice + ($direction * $delta);

        $finalPrice = $adjusted > 0 ? $adjusted : $marketPrice;

        return $this->applyBotPriceRange($pair, $finalPrice);
    }
    private function getBotPricePercent(float $price): float
    {
        // Keep the change very small and scale slightly by price magnitude.
        $percentMin = 0.00001; // 0.001%
        $percentMax = 0.00005; // 0.005%

        if ($price >= 10000) {
            $percentMin = 0.000005; // 0.0005%
            $percentMax = 0.00003;  // 0.003%
        } elseif ($price >= 1000) {
            $percentMin = 0.000008; // 0.0008%
            $percentMax = 0.00004;  // 0.004%
        }

        $min = (int) ($percentMin * 1_000_000_000);
        $max = (int) ($percentMax * 1_000_000_000);
        if ($max < $min) {
            $max = $min;
        }

        return mt_rand($min, $max) / 1_000_000_000;
    }

    private function getBotPriceTick(float $price): float
    {
        if ($price >= 10000) {
            return 0.1;
        }
        if ($price >= 1000) {
            return 0.01;
        }
        if ($price >= 100) {
            return 0.001;
        }
        if ($price >= 10) {
            return 0.0001;
        }
        if ($price >= 1) {
            return 0.00001;
        }
        if ($price >= 0.1) {
            return 0.000001;
        }

        return 0.0000001;
    }

    private function roundToTick(float $value, float $tick): float
    {
        if ($tick <= 0) {
            return $value;
        }

        $steps = (int) round($value / $tick);
        if ($steps < 1) {
            $steps = 1;
        }

        return $steps * $tick;
    }

    public function generateCustomBotAmount(FutureCoinPair|BotCoinPairDto $pair): float
    {
        $minAmount = (float) ($pair->botSetting?->amount_low ?? 0);
        $maxAmount = (float) ($pair->botSetting?->amount_high ?? 0);

        if ($minAmount <= 0) {
            $minAmount = (float) ($pair->min_amount ?? 0);
        }
        if ($maxAmount <= 0) {
            $maxAmount = (float) ($pair->max_amount ?? 0);
        }

        if ($minAmount > 0) {
            if ($maxAmount <= 0 || $maxAmount < $minAmount) {
                $maxAmount = $minAmount;
            }

            $min = (int) ($minAmount * (10 ** 10));
            $max = (int) ($maxAmount * (10 ** 10));

            if ($max < $min) {
                $max = $min;
            }

            return mt_rand($min, $max) / (10 ** 10);
        }

        $result = 0.0;
        try {
            $usdPrice = (float) ($pair->trade_coin_usd_rate ?? $pair->tradeCoin?->coin_price ?? 0);
            if ($usdPrice <= 0) {
                $usdPrice = 1;
            }

            $dividendFactor = random_int(40, 100);
            $result = (float) bcdivx((string) $dividendFactor, (string) $usdPrice, 8);
        } catch (\Throwable $th) {
            storeException('futureGenerateCustomBotAmount ' . $pair->id, $th->getMessage());
        }

        return $result;
    }

    private function applyBotPriceRange(FutureCoinPair $pair, float $price): float
    {
        if ($price <= 0) {
            return 0;
        }

        $priceLow = (float) ($pair->botSetting?->price_low ?? 0);
        $priceHigh = (float) ($pair->botSetting?->price_high ?? 0);

        if ($priceLow > 0 && $priceHigh > 0 && $priceHigh >= $priceLow) {
            if ($price < $priceLow || $price > $priceHigh) {
                $min = (int) ($priceLow * (10 ** 8));
                $max = (int) ($priceHigh * (10 ** 8));
                if ($max < $min) {
                    $max = $min;
                }

                return mt_rand($min, $max) / (10 ** 8);
            }
        }

        return $price;
    }

    private function makeBotOrderData(
        FutureCoinPair $coinPair,
        int $user_id,
        OrderType $orderType,
        MarginModeEnum $marginMode,
        int $leverage,
        float $price,
        float $amount
    ): FutureOrderData {
        $orderData = new FutureOrderData(
            coinPair: $coinPair,
            user_id: $user_id,
            margin_mode: $marginMode,
            order_method: OrderMethod::LIMIT,
            order_type: $orderType,
            future_coin_pair_id: $coinPair->id,
            trade_coin_id: $coinPair->trade_coin_id,
            base_coin_id: $coinPair->base_coin_id,
            coin_pair_uid: $coinPair->uid,
            leverage: $leverage,
            price: $price,
            amount: $amount,
            tp_price: 0,
            sl_price: 0,
            stop_price: 0,
            is_reduce: 0,
            uid: Str::uuid()->getHex(),
        );

        $scale = $coinPair->trade_decimal ?: 8;
        $totalPrice = bcmulx((string) $price, (string) $amount, $scale);

        $orderData->setProperty('is_bot', 1, true);
        $orderData->setProperty('pending_amount', $amount, true);
        $orderData->setProperty('market_price', $price, true);
        $orderData->setProperty('mark_price', $price, true);
        $orderData->setProperty('index_price', $price, true);
        $orderData->setProperty('total_price', $totalPrice, true);

        return $orderData;
    }

    public function matchEngine(FutureBuy|FutureSell $order)
    {
        $this->repository->setModel($order->order_type);
        $user = User::find($order->user_id);
        if (!$user) {
            ResponseFacade::failed(__('User not found'))->safeThrow();
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
                        if($matchOrder->is_bot){
                            $this->handelBotMatchedOrder(
                                order: $order,
                                matchOrder: $matchOrder
                            );
                        } else {
                            $this->orderService->handelMatchedOrder(
                                order: $order,
                                matchOrder: $matchOrder
                            );
                        }
                    }

                    if (is_null($orders->next())) {
                        break;
                    }
                }
            },
            [OrderType::BUY, OrderType::SELL]
        );
    }

    private function handelBotMatchedOrder(FutureBuy|FutureSell $order, $matchOrder)
    {
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
            debugLogger('bot order lock from order matching engine');
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
                errorLogger('996479: Buy or Sell amount not valid');
                DB::rollBack();
                return;
            }

            $amountToBeProcessed = 0;
            if (bccompx($buyAbleAmount, $sellAbleAmount) != 1) {
                $amountToBeProcessed = $buyAbleAmount;
            } else {
                $amountToBeProcessed = $sellAbleAmount;
            }

            $tradeData = FutureTradeData::fromOrderPrecess(
                order: $order,
                matchOrder: $matchOrder,
                amountToBeProcessed: $amountToBeProcessed,
                maker_fees: 0,
                taker_fees: 0
            );
            $tradeData->setProperty('is_bot', 1, true);

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

            DB::commit();

            // set market price on cache (after commit)
            cache_service()->setMarketPrice($order->coin_pair->id, $tradeData->price);

            rescue(function () use ($order, $tradeData, $matchOrder) {
                OrderBookBroadcastEvent::dispatch($order->coin_pair->uid, $matchOrder);
                TodayChangeBroadcastEvent::dispatch($order->coin_pair->id);
                TradeBroadcastEvent::dispatch($order->coin_pair->uid, $order->coin_pair->code, $tradeData);
            });

            // update chart candle
            $chartService = new TradingViewChartService();
            $tradeData->coinPair = $order->coin_pair;
            $chartService->updateCandleData($tradeData);
        } catch (\Throwable $th) {
            DB::rollBack();
            debugLogger('Order: increment and decrement failed to update: ' . $th->getMessage());
            throw $th;
        }
    }

        /**
     * Summary of getBestBuyPrice
     * @param float $price
     * @return float
     */
    protected function getBestBuyPrice(float $price): float
    {
        $buyOrder = FutureBuy::select(DB::raw("MIN(price) as price, SUM(pending_amount) as amount"))->where([
            "trade_coin_id" => $this->coinPair->trade_coin_id,
            "base_coin_id"  => $this->coinPair->base_coin_id,
            "status"        => 0,
        ])->where("price", ">=", $price)->first();

        return $buyOrder?->amount ?: $this->generateCustomBotAmount($this->coinPair);
    }

    /**
     * Summary of getBestSellPrice
     * @param float $price
     * @return float
     */
    protected function getBestSellPrice(float $price): float
    {
        $sellOrder = FutureSell::select(DB::raw("MIN(price) as price, SUM(pending_amount) as amount"))->where([
            "trade_coin_id" => $this->coinPair->trade_coin_id,
            "base_coin_id"  => $this->coinPair->base_coin_id,
            "status"        => 0,
        ])->where("price", "<=", $price)->first();

        return $sellOrder?->amount ?: $this->generateCustomBotAmount($this->coinPair);

    }
}
