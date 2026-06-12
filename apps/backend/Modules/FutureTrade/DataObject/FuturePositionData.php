<?php

namespace Modules\FutureTrade\DataObject;

use Illuminate\Support\Str;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Services\MathService\PositionMath;
use Modules\FutureTrade\Services\OrderService\OrderService;

class FuturePositionData
{
    public $hidden_vars_value = ['hidden_vars_value', 'coinPair'];

    private $allow_vars_value = [];

    public PositionStatusEnum $status = PositionStatusEnum::OPEN;

    public function __construct(
        public FutureCoinPair $coinPair,
        public ?OrderType $order_type,
        public ?MarginModeEnum $margin_mode,
        public int $user_id,
        public int $future_wallet_id,
        public int $future_coin_pair_id,
        public string $future_coin_pair_uid,
        public int $trade_coin_id,
        public int $base_coin_id,
        public string|float $margin_balance,
        public int $leverage,
        public float|string $price,
        public float|string $amount,
        public ?float $tp_price,
        public ?float $sl_price,
        public ?string $uid,
    ) {
        $this->amount = $this->order_type == OrderType::BUY
            ? $amount
            : bcmulx(-1, $amount, ($this->coinPair->base_decimal ?? 8) + 10);
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

            // Allow value will be set in array
            if (in_array($key, $this->allow_vars_value)) {
                $newData[$key] = ($value instanceof \BackedEnum) ? $value?->value : (($value instanceof \UnitEnum) ? $value?->name : $value);

                continue;
            }

            // Filter Values And Make New Array
            elseif (!$value) {
                continue;
            }
            $newData[$key] = ($value instanceof \BackedEnum) ? $value?->value : (($value instanceof \UnitEnum) ? $value?->name : $value);
        }

        return $newData;
    }

    public function toArrayForUpdate(?array $data = null): array
    {
        $data ??= [];

        if ($this->order_type) {
            $data['order_type'] = $this->order_type->value;
        }
        if ($this->tp_price) {
            $data['take_profit_price'] = $this->tp_price;
        }
        if ($this->sl_price) {
            $data['stop_loss_price'] = $this->sl_price;
        }

        return $data;
    }

    public static function fromTrade(FutureTrade $futureTrade, FutureWallet $wallet): ?FuturePositionData
    {
        if (!$futureTrade->coinPair) {
            debugLogger('Coin pair not found i future Table in positon data object from trade data');
            return null;
        }

        /** @var FutureCoinPair $coin_pair */
        $coin_pair = $futureTrade->coinPair;

        // user id need in PositionMath::initialMargin method
        $futureTrade->user_id = $wallet->user_id;

        return new self(
            coinPair: $coin_pair,
            order_type: $futureTrade->orderType,
            margin_mode: OrderService::validateUserLeverage($coin_pair, $wallet->user_id)->margin_mode,
            user_id: $wallet->user_id,
            future_wallet_id: $wallet->id,
            future_coin_pair_id: $coin_pair->id,
            future_coin_pair_uid: $coin_pair->uid,
            trade_coin_id: $coin_pair->trade_coin_id,
            base_coin_id: $coin_pair->base_coin_id,
            margin_balance: PositionMath::initialMargin($futureTrade),
            leverage: OrderService::validateUserLeverage($coin_pair, $wallet->user_id)->leverage,
            price: $futureTrade->price,
            amount: $futureTrade->amount,
            tp_price: $futureTrade->take_profit_price,
            sl_price: $futureTrade->stop_loss_price,
            uid: Str::uuid()->getHex(),
        );
    }
}
