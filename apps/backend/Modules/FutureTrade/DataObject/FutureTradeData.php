<?php

namespace Modules\FutureTrade\DataObject;

use Illuminate\Support\Str;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\CollateralTypeEnum;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderRequest;
use Modules\FutureTrade\Services\OrderService\OrderService;
use Modules\FutureTrade\Services\TradeServices\TradeService;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

class FutureTradeData
{
    private $allow_vars_value = [];
    public float|string $total_price = 0;
    public ?int $status = 0;
    public ?int $is_bot = 0;

    public CollateralTypeEnum $collateral_type = CollateralTypeEnum::USDT_M;

    public function __construct(
        public OrderType $order_type,
        public int $future_coin_pair_id,
        public int $trade_coin_id,
        public int $base_coin_id,
        public int $buy_id,
        public int $sell_id,
        public float $price,
        public float $maker_fees,
        public float $taker_fees,
        public float $amount,
        public float $last_price = 0,
        public ?string $uid,
        public ?int $buyer_id,
        public ?int $seller_id,
        public ?int $maker_id,
        public ?int $taker_id,
    ) {
        $this->total_price = bcmulx($this->price, $this->amount, 20);
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

    public static function fromOrderPrecess(
        FutureBuy|FutureSell $order,
        FutureBuy|FutureSell $matchOrder,
        float $amountToBeProcessed,
        float $maker_fees,
        float $taker_fees
    ): FutureTradeData {
        return new self(
            order_type: $order->order_type,
            future_coin_pair_id: $order?->coin_pair->id,
            trade_coin_id: $order?->coin_pair->trade_coin_id,
            base_coin_id: $order?->coin_pair->base_coin_id,
            buy_id: $order->order_type == OrderType::BUY ? $order->id : $matchOrder->id,
            sell_id: $order->order_type == OrderType::SELL ? $order->id : $matchOrder->id,
            price: $matchOrder->price,
            maker_fees: $maker_fees,
            taker_fees: $taker_fees,
            amount: $amountToBeProcessed,
            last_price: cache_service()->getMarketPrice($order?->coin_pair->id ?: 0) ?: 0,
            uid: Str::uuid()->getHex(),
            buyer_id: $order->order_type == OrderType::BUY ? $order->user_id : $matchOrder->user_id,
            seller_id: $order->order_type == OrderType::SELL ? $order->user_id : $matchOrder->user_id,
            maker_id: $matchOrder->user_id,
            taker_id: $order->user_id,
        );
    }

    public function create(): FutureTrade
    {
        $service = app(TradeService::class);
        return $service->createNewTrade($this);
    }
}
