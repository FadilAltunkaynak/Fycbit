<?php

namespace Modules\FutureTrade\DataObject;

use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureSell;

class FutureMatchOrder
{
    private $allow_vars_value = [];
    public int $coin_pair_id;
    public float $price;
    public OrderType $order_type;
    public float $amount;

    public static function fromCachedOrder(array $order): self
    {
        $data = new self();
        $data->price = $order['price'];
        $data->amount = $order['amount'];
        $data->order_type = OrderType::from($order['order_type']);
        $data->coin_pair_id = $order['coin_pair_id'];
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
