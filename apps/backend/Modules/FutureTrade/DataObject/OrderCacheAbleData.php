<?php

namespace Modules\FutureTrade\DataObject;

use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureSell;

class OrderCacheAbleData
{
    public $allow_vars_value = [];
    public $hidden_vars_value = [];

    public int $user_id;
    public int $coin_pair_id;

    public float $price;
    public OrderType $order_type;

    public float $amount;

    public static function fromOrder(FutureBuy|FutureSell $order): self
    {
        $data = new self();
        $data->price = $order->price;
        $data->amount = $order->total_pending ?? $order->pending_amount;
        $data->order_type = $order->order_type;
        $data->coin_pair_id = $order->future_coin_pair_id;
        return $data;
    }

    public function toArray()
    {
        $all = get_object_vars($this);
        $newData = [];
        foreach ($all as $key => $value) {
            // Allow value will be set in array
            if(in_array($key, $this->allow_vars_value))
                $newData[$key] = ($value instanceof \UnitEnum) ? $value?->value : $value;

            // Filter Values And Make New Array
            elseif(!$value) continue;
                $newData[$key] = ($value instanceof \UnitEnum) ? $value?->value : $value;
        }
        return $newData;
    }
}
