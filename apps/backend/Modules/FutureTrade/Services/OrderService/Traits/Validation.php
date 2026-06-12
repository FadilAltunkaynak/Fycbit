<?php

namespace Modules\FutureTrade\Services\OrderService\Traits;

use App\Facades\ResponseFacade;
use App\Model\Coin;
use Deprecated;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureUserSetting;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;
use Modules\FutureTrade\Repositories\PositionRepository\PositionRepository;
use Modules\FutureTrade\Services\MathService\LeverageMath;
use Modules\FutureTrade\Services\OrderService\OrderService;

trait Validation
{
    use LimitOrderValidation,
        MarketOrderValidation,
        StopLimitValidation;

    /**
     * Validate Coin Pair
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function validateCoinPair(string $coin_pair_uid): FutureCoinPair
    {
        // Check Coin Pair
        /** @var FutureCoinPair $coin_pair */
        $coin_pair = FutureCoinPair::query()
            ->with(['tradeCoin', 'baseCoin'])
            ->where('uid', $coin_pair_uid)
            ->first();

        if (!$coin_pair) {
            ResponseFacade::failed('Coin pair not found')->safeThrow();
        }

        if (!$coin_pair->tradeCoin) {
            ResponseFacade::failed('Trade coin not found')->safeThrow();
        }

        if (!$coin_pair->baseCoin) {
            ResponseFacade::failed('Base coin not found')->safeThrow();
        }

        return $coin_pair;
    }

    public function validateUserMargin(FutureCoinPair $coin_pair, int $margin_mode, ?int $user_id = null): array
    {
        // Check User Margin
        $marginMode = MarginModeEnum::from($margin_mode);
        $margin = $this->getUserMarginMode($coin_pair->id, $user_id);
        $this->validateUserMarginMode($margin, $marginMode);

        return [$margin, $marginMode];
    }

    /**
     * Validate Minimum and Maximum Order Amount
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMinAndMaxAmount(FutureCoinPair $coin_pair, float $amount): bool
    {
        return $this->checkMinimumAmount($coin_pair, $amount)
            && $this->checkMaximumAmount($coin_pair, $amount);
    }

    /**
     * Validate Order Minimum Amount
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMinimumAmount(FutureCoinPair $coin_pair, float $amount): bool
    {
        if (!$coin_pair->minimum_amount) {
            return true;
        }

        if ($amount < $coin_pair->minimum_amount) {
            ResponseFacade::failed('Amount is less than minimum amount')->safeThrow();
        }

        return true;
    }

    /**
     * Validate Order Maximum Amount
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMaximumAmount(FutureCoinPair $coin_pair, float $amount): bool
    {
        if (!$coin_pair->maximum_amount) {
            return true;
        }

        if ($amount > $coin_pair->maximum_amount) {
            ResponseFacade::failed('Amount is greater than maximum amount')->safeThrow();
        }

        return true;
    }

    /**
     * Validate Take Profit and Stop Loss Order Price
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function takeProfitAndStopLossValidation(
        OrderType $orderType,
        float $price,
        float $take_profit_price,
        float $stop_loss_price
    ): bool {
        return $this->takeProfitValidation($orderType, $price, $take_profit_price)
            && $this->stopLossValidation($orderType, $price, $stop_loss_price);
    }

    /**
     * Validate Take Profit Order Price
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function takeProfitValidation(OrderType $orderType, float|string $price, float|string $take_profit_price): bool
    {
        if (!$take_profit_price) {
            return true;
        }

        $message = [
            OrderType::BUY->value => __('Take Profit Price should be greater than order price'),
            OrderType::SELL->value => __('Take Profit Price should be less than order price'),
        ];

        $result = match ($orderType) {
            OrderType::BUY => $take_profit_price <= $price,
            OrderType::SELL => $take_profit_price >= $price,
        };

        if ($result) {
            ResponseFacade::failed($message[$orderType->value])->safeThrow();
        }

        return true;
    }

    /**
     * Validate Stop Loss Order Price
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function stopLossValidation(OrderType $orderType, float|string $price, float|string $stop_loss_price): bool
    {
        if (!$stop_loss_price) {
            return true;
        }

        $message = [
            OrderType::BUY->value => __('Stop-Loss Price should be less than order price'),
            OrderType::SELL->value => __('Stop-Loss Price should be greater than order price'),
        ];

        $result = match ($orderType) {
            OrderType::BUY => $stop_loss_price >= $price,
            OrderType::SELL => $stop_loss_price <= $price,
        };

        if ($result) {
            ResponseFacade::failed($message[$orderType->value])->safeThrow();
        }

        return true;
    }

    /**
     * Validate User Margin Mode
     *
     * @param  mixed  $userMargin
     *                             use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function validateUserMarginMode(MarginModeEnum $margin, ?MarginModeEnum $userMargin): bool
    {
        if ($margin !== $userMargin) {
            ResponseFacade::failed('User margin mode invalid')->safeThrow();
        }

        return true;
    }

    /**
     * Get User Margin Mode From DB
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function getUserMarginMode(int $coin_pair_id, ?int $user_id = null): MarginModeEnum
    {
        $user_id ??= authId();
        $setting = FutureUserSetting::where([
            'user_id' => $user_id,
            'coin_pair_id' => $coin_pair_id,
        ])->first();

        if (!$setting) {
            ResponseFacade::failed('User margin setting not found')->safeThrow();
        }

        if (!$setting?->margin_mode instanceof MarginModeEnum) {
            ResponseFacade::failed('User margin mode not found')->safeThrow();
        }

        return $setting->margin_mode;
    }

    /**
     * Validate Minimum and Maximum Order Price
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMinAndMaxOrderPrice(FutureCoinPair $coin_pair, float $market_price, float $price): bool
    {
        return $this->checkMinimumPrice($coin_pair, $market_price, $price)
            && $this->checkMaximumPrice($coin_pair, $market_price, $price);
    }

    /**
     * Validate Minimum Order Price
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMinimumPrice(FutureCoinPair $coin_pair, float $market_price, float $price): bool
    {
        if (!$coin_pair->minimum_price) {
            return true;
        }

        $percent = bcdivx($coin_pair->minimum_price, 100);
        $percentPrice = bcmulx($market_price, $percent);
        $minPrice = bcsubx($market_price, $percentPrice);

        if ($price < $coin_pair->minimum_price) {
            ResponseFacade::failed('Price is less than minimum price')->safeThrow();
        }

        return true;
    }

    /**
     * Validate Maximum Order Price
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMaximumPrice(FutureCoinPair $coin_pair, float $market_price, float $price): bool
    {
        if (!$coin_pair->maximum_price) {
            return true;
        }

        $percent = bcdivx($coin_pair->maximum_price, 100);
        $percentPrice = bcmulx($market_price, $percent);
        $maxPrice = bcaddx($market_price, $percentPrice);

        if ($price > $maxPrice) {
            ResponseFacade::failed('Price is greater than maximum price')->safeThrow();
        }

        return true;
    }

    public static function validateUserLeverage(FutureCoinPair $coin_pair, ?int $user_id = null): FutureUserSetting
    {
        $user_id ??= authId();
        $setting = FutureUserSetting::where([
            'user_id' => $user_id,
            'coin_pair_id' => $coin_pair->id,
        ])->first();

        if (!$setting) {
            ResponseFacade::failed('User leverage setting not found')->safeThrow();
        }

        if (!$setting?->leverage) {
            ResponseFacade::failed('User leverage not found')->safeThrow();
        }

        return $setting;
    }

    public function validateFutureWalletBalance(Coin $coin, float|string $amount, ?int $user_id = null): bool
    {
        $wallet = FutureWallet::where('user_id', $user_id ?: authId())
            ->where('coin_id', $coin->id)->first();

        if (!$wallet) {
            ResponseFacade::failed('Wallet not found')->safeThrow();
        }

        if ($wallet->balance < $amount) {
            ResponseFacade::failed('Wallet has not enough balance')->safeThrow();
        }

        return true;
    }

    public function maxOpenOrderValidation(FutureCoinPair $coinPair, ?int $user_id = null): bool
    {
        $user_id ??= authId();
        $total_orders = 0;
        $max_orders = $coinPair?->max_open_orders ?? 0;

        if (!$max_orders) {
            return true;
        }

        array_map(function ($model) use (&$total_orders, $user_id, $coinPair) {
            $order_count = $model::selectRaw('COUNT(*) as total')
                ->where('future_coin_pair_id', $coinPair->id)
                ->where('user_id', $user_id)
                ->where('status', OrderStatusEnum::PENDING->value)
                ->first();

            $total_orders += $order_count->total ?? 0;
        }, [FutureSell::class, FutureBuy::class]);

        if ($max_orders < $total_orders) {
            ResponseFacade::failed(__('Your order limit exceed. Max limit order is :order', ['order' => $max_orders]))->safeThrow();
        }

        return true;
    }

    public function reduceOnlyValidation(FutureOrderData &$orderData)
    {
        $openPosition = PositionRepository::getPosition(
            coin_pair_id: $orderData->coinPair->id,
            user_id: $orderData->user_id
        );

        if (!$openPosition) {
            ResponseFacade::failed('Position already closed')->safeThrow();
        }

        if ($openPosition?->amount == 0) {
            ResponseFacade::failed('Position already closed')->safeThrow();
        }

        if ($orderData->order_type == $openPosition->order_type) {
            ResponseFacade::failed('Reduce only order is rejected')->safeThrow();
        }

        if ($orderData->amount == 0) {
            $orderData->amount = abs($openPosition->amount);
        }

        $openReduceOrder = app(IOrderRepository::class)->getPendingOrdersBuilder(
            coinPair: $orderData->coinPair?->id ?? 0,
            orderType: $orderData->order_type
        )
            ->where('user_id', $orderData->user_id)
            ->where('is_reduce', true)->first();

        if ($openReduceOrder) {
            ResponseFacade::failed('You already have a reduce only order')->safeThrow();
        }

        if (abs($openPosition->amount) < $orderData->amount) {
            ResponseFacade::failed('Reduce only order amount is higher than position amount')->safeThrow();
        }
    }

    private function validateProcessAndPendingAmount(FutureBuy $buy, FutureSell $sell, float|string $amountToBeProcessed): bool
    {
        if (bcaddx($buy->processed_amount, $amountToBeProcessed, ($buy?->coin_pair->trade_decimal ?? 8) + 10) > $buy->amount) {
            $message = __('Buy order processed amount not valid');
            errorLogger($message, [
                'processed_amount' => $buy->processed_amount,
                'amountToBeProcessed' => $amountToBeProcessed,
                'buyAmount' => $buy->amount,
            ]);
            ResponseFacade::failed($message)->safeThrow();
        }

        if (bcaddx($buy->pending_amount, $amountToBeProcessed, ($buy?->coin_pair->trade_decimal ?? 8) + 10) < 0) {
            $message = __('Buy order pending amount not valid');
            errorLogger($message, [
                'pending_amount' => $buy->pending_amount,
                'amountToBeProcessed' => $amountToBeProcessed,
            ]);
            ResponseFacade::failed($message)->safeThrow();
        }

        if (bcaddx($buy->processed_amount, $buy->pending_amount, ($buy?->coin_pair->trade_decimal ?? 8) + 10) < 0) {
            $message = __('Buy order processed and pending amount not valid');
            errorLogger($message, [
                'processed_amount' => $buy->processed_amount,
                'pending_amount' => $buy->pending_amount,
            ]);
            ResponseFacade::failed($message)->safeThrow();
        }

        if (bcaddx($sell->processed_amount, $amountToBeProcessed, ($buy?->coin_pair->trade_decimal ?? 8) + 10) > $sell->amount) {
            $message = __('Sell order processed amount not valid');
            errorLogger($message, context: [
                'processed_amount' => $sell->processed_amount,
                'amountToBeProcessed' => $amountToBeProcessed,
                'sellAmount' => $sell->amount,
            ]);
            ResponseFacade::failed($message)->safeThrow();
        }

        if (bcaddx($sell->pending_amount, $amountToBeProcessed, ($buy?->coin_pair->trade_decimal ?? 8) + 10) < 0) {
            $message = __('Sell order pending amount not valid');
            errorLogger($message, context: [
                'pending_amount' => $sell->pending_amount,
                'amountToBeProcessed' => $amountToBeProcessed,
            ]);
            ResponseFacade::failed($message)->safeThrow();
        }

        if (bcaddx($sell->processed_amount, $sell->pending_amount, ($buy?->coin_pair->trade_decimal ?? 8) + 10) < 0) {
            $message = __('Sell order processed and pending amount not valid');
            errorLogger($message, [
                'processed_amount' => $sell->processed_amount,
                'pending_amount' => $sell->pending_amount,
            ]);
            ResponseFacade::failed($message)->safeThrow();
        }

        return true;
    }

    public function getOrderCost(FutureOrderData $orderData): string|float
    {
        $decimal = $orderData?->coinPair?->trade_decimal ?? 8;
        $leverage = $orderData->leverage;
        $amount = $orderData->amount;

        $initial_margin = LeverageMath::initial_margin(
            price: $orderData->price,
            amount: $amount,
            leverage: $leverage,
            scale: $decimal
        );

        [$maker_fees, $taker_fees] = OrderService::getTakerAndMakerFees(
            coinPair: $orderData->coinPair,
            price: $orderData->price,
            processed_amount: $orderData->amount
        );

        $order_cost = bcaddx($initial_margin, $taker_fees);

        return $order_cost;
    }

    /**
     * Get Pending Orders Cost Buy/Sell
     *
     * @return array<int|string>
     */
    public function getOpenOrderCost(FutureCoinPair $coin_pair, ?int $user_id = null): array
    {
        $user_id ??= authId();
        $sell_cost = 0;
        $buy_cost = 0;

        array_map(
            function ($model) use (&$sell_cost, &$buy_cost, $coin_pair, $user_id) {
                $orders = $model::query()
                    ->where('future_coin_pair_id', $coin_pair->id)
                    ->where('user_id', $user_id)
                    ->pendingOrder()
                    ->get();

                foreach ($orders as $order) {
                    $order->amount = $order->pending_amount;
                    $orderData = FutureOrderData::fromOrder(
                        order: $order,
                        coin_pair: $coin_pair
                    );

                    $cost = $this->getOrderCost($orderData);
                    if ($model == FutureBuy::class) {
                        $buy_cost = bcaddx($buy_cost, $cost, $coin_pair->trade_decimal ?: 8);
                    } else {
                        $sell_cost = bcaddx($sell_cost, $cost, $coin_pair->trade_decimal ?: 8);
                    }
                }
            },
            [FutureBuy::class, FutureSell::class]
        );

        // 0 => sell cost
        // 1 => buy cost
        return [$sell_cost, $buy_cost];
    }

    /**
     * Get Open Position Cost
     *
     * @return array<?FuturePosition|string>
     */
    public function getPositionCost(FutureCoinPair $coin_pair, ?int $user_id = null): array
    {
        $user_id ??= authId();

        /** @var FuturePosition $position */
        $position = PositionRepository::getPosition(
            coin_pair_id: $coin_pair->id ?? 0,
            user_id: $user_id
        );

        if (!$position) {
            // 0 => initial_margin
            // 1 => Close Order Fees
            // 2 => FuturePosition object
            return [0, 0, null];
        }

        [$maker_fees, $taker_fees] = OrderService::getTakerAndMakerFees(
            coinPair: $coin_pair,
            price: $position->price,
            processed_amount: abs($position->amount)
        );

        $initial_margin = LeverageMath::initial_margin(
            price: $position->price,
            amount: abs($position->amount),
            leverage: $position->leverage,
            scale: $coin_pair->trade_decimal ?: 8
        );

        // 0 => initial_margin
        // 1 => Close Order Fees
        // 2 => FuturePosition object
        return [$initial_margin, $taker_fees, $position];
    }

    #[Deprecated]
    public function getAndValidateTotalCost(FutureOrderData $orderData): string
    {
        $decimal = $orderData?->coinPair->trade_decimal ?: 8;

        $orderCost = $this->getOrderCost($orderData);

        [$sell_orders_cost, $buy_orders_cost] = $this->getOpenOrderCost(
            $orderData->coinPair,
            $orderData->user_id
        );

        [$positionCost, $positionFees, $openPosition] = $this->getPositionCost(
            $orderData->coinPair,
            $orderData->user_id
        );

        if ($orderData->order_type == OrderType::BUY) {
            $buy_orders_cost = bcaddx($buy_orders_cost, $orderCost, $decimal);
        } else {
            $sell_orders_cost = bcaddx($sell_orders_cost, $orderCost, $decimal);
        }

        if (
            $openPosition &&
            $orderData->order_type == $openPosition?->order_type &&
            $openPosition->order_type == OrderType::BUY
        ) {
            $buy_orders_cost = bcaddx($buy_orders_cost, $positionCost, $decimal);
        }

        if (
            $openPosition &&
            $orderData->order_type == $openPosition?->order_type &&
            $openPosition->order_type == OrderType::SELL
        ) {
            $sell_orders_cost = bcaddx($sell_orders_cost, $positionCost, $decimal);
        }

        if (
            $openPosition &&
            $orderData->order_type !== $openPosition?->order_type &&
            $openPosition->order_type == OrderType::BUY
        ) {
            $sell_orders_cost = bcsubx(
                $sell_orders_cost > $positionCost ? $sell_orders_cost : $positionCost,
                $sell_orders_cost > $positionCost ? $positionCost : $sell_orders_cost,
                $decimal
            );

            if ($sell_orders_cost < $positionFees) {
                $sell_orders_cost = $positionFees;
            }
        }

        if (
            $openPosition &&
            $orderData->order_type !== $openPosition?->order_type &&
            $openPosition->order_type == OrderType::SELL
        ) {
            $buy_orders_cost = bcsubx(
                $buy_orders_cost > $positionCost ? $buy_orders_cost : $positionCost,
                $buy_orders_cost > $positionCost ? $positionCost : $buy_orders_cost,
                $decimal
            );

            if ($buy_orders_cost < $positionFees) {
                $buy_orders_cost = $positionFees;
            }
        }

        return bcaddx($sell_orders_cost, $buy_orders_cost, $decimal);
    }
}
