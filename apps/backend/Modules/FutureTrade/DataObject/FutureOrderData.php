<?php

namespace Modules\FutureTrade\DataObject;

use Illuminate\Support\Str;
use Modules\FutureTrade\Emum\CollateralTypeEnum;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\TpSlType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderRequest;
use Modules\FutureTrade\Repositories\PositionRepository\PositionRepository;
use Modules\FutureTrade\Services\MathService\LeverageMath;
use Modules\FutureTrade\Services\OrderService\OrderService;

class FutureOrderData
{
    public $hidden_vars_value = ["coinPair", "process_stop_limit", 'position'];
    public $exclude_stop_limit_vars_value = ["stop_price"];
    private $allow_vars_value = [];
    public float|string $total_price = 0;
    public float|string $pending_amount = 0;
    public float|string $market_price = 0;
    public float|string $mark_price = 0;
    public float|string $index_price = 0;
    public TpSlType $tpsl_type = TpSlType::NONE;
    public ?int $status = OrderStatusEnum::PROCESSING->value;
    public ?int $is_bot = 0;
    public bool $process_stop_limit = false;
    public ?FuturePosition $position = null;
    public CollateralTypeEnum $collateral_type = CollateralTypeEnum::USDT_M;

    public function __construct(
        public FutureCoinPair $coinPair,
        public int $user_id,
        public MarginModeEnum $margin_mode,
        public OrderMethod $order_method,
        public OrderType $order_type,
        public int $future_coin_pair_id,
        public int $trade_coin_id,
        public int $base_coin_id,
        public string $coin_pair_uid,
        public int $leverage,
        public float $price,
        public float $amount,
        public ?float $tp_price,
        public ?float $sl_price,
        public ?float $stop_price,
        public ?int $is_reduce,
        public ?string $uid,
    ) {
    }

    public function setProperty($name, $value, $force = false)
    {
        if (!property_exists($this, $name)) {
            return;
        }
        if ($value || $force) {
            $this->$name = $value;
        }
    }

    public function toArray(): array
    {
        $all = get_object_vars($this);
        $newData = [];
        foreach ($all as $key => $value) {
            // Ignore Hidden Values And Make
            if (in_array($key, $this->hidden_vars_value)) {
                continue;
            }

            if (
                $this->order_method !== OrderMethod::STOP_LIMIT &&
                in_array($key, $this->exclude_stop_limit_vars_value)
            ) {
                continue;
            }

            // Allow value will be set in array
            if (in_array($key, $this->allow_vars_value)) {
                $newData[$key] = ($value instanceof \BackedEnum) ? $value?->value : (($value instanceof \UnitEnum) ? $value?->name : $value);
            }

            // Filter Values And Make New Array
            elseif (!$value) {
                continue;
            }
            $newData[$key] = ($value instanceof \BackedEnum) ? $value?->value : (($value instanceof \UnitEnum) ? $value?->name : $value);
        }

        return $newData;
    }

    public static function fromOrderRequest(FutureOrderRequest $request, FutureCoinPair $coin_pair): FutureOrderData
    {
        return new self(
            coinPair: $coin_pair,
            user_id: $request->user()->id,
            margin_mode: MarginModeEnum::from($request->margin_mode),
            order_method: OrderMethod::from($request->order_method),
            order_type: OrderType::from($request->order_type),
            future_coin_pair_id: $coin_pair->id,
            trade_coin_id: $coin_pair->trade_coin_id,
            base_coin_id: $coin_pair->base_coin_id,
            coin_pair_uid: $coin_pair->uid,
            leverage: OrderService::validateUserLeverage($coin_pair)->leverage,
            price: $request->price,
            amount: $request->amount ?? 0,
            tp_price: $request->take_profit_price,
            sl_price: $request->stop_loss_price,
            stop_price: $request->stop_price,
            is_reduce: (bool) $request->is_reduce,
            uid: $request->uid ?? Str::uuid()->getHex(),
        );
    }

    public static function fromOrder(FutureBuy|FutureSell $order, FutureCoinPair $coin_pair)
    {
        $orderData = new self(
            coinPair: $coin_pair,
            user_id: $order->user_id,
            margin_mode: MarginModeEnum::from($order->margin_mode),
            order_method: OrderMethod::LIMIT,
            order_type: $order->order_type,
            future_coin_pair_id: $order->future_coin_pair_id,
            trade_coin_id: $order->trade_coin_id,
            base_coin_id: $order->base_coin_id,
            coin_pair_uid: $coin_pair->uid,
            leverage: OrderService::validateUserLeverage($coin_pair)->leverage,
            price: $order->price,
            amount: $order->amount,
            tp_price: $order->tp_price,
            sl_price: $order->sl_price,
            stop_price: 0,
            is_reduce: false,
            uid: $order->uid,
        );

        return $orderData;
    }

    /**
     * Return current open position of relative coin pair
     */
    public function getCurrentOpenPosition(): ?FuturePosition
    {
        if ($this->position) {
            return $this->position;
        }
        return $this->position = PositionRepository::getPosition(
            coin_pair_id: $this?->coinPair->id ?? 0,
            user_id: $this->user_id
        );
    }

    public static function fromTpSlOrderMaker(FuturePosition $position, OrderType $orderType): self
    {
        return new self(
            coinPair: $position->coin_pair,
            user_id: $position->user_id,
            margin_mode: $position->margin_mode,
            order_method: OrderMethod::MARKET,
            order_type: $orderType,
            future_coin_pair_id: $position->coin_pair->id,
            trade_coin_id: $position->coin_pair->trade_coin_id,
            base_coin_id: $position->coin_pair->base_coin_id,
            coin_pair_uid: $position->coin_pair->uid,
            leverage: OrderService::validateUserLeverage($position->coin_pair, $position->user_id)->leverage,
            price: 0,
            amount: abs($position->amount),
            tp_price: 0,
            sl_price: 0,
            stop_price: 0,
            is_reduce: true,
            uid: Str::uuid()->getHex(),
        );
    }
}
