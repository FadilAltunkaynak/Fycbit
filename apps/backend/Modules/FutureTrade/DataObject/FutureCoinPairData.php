<?php

namespace Modules\FutureTrade\DataObject;

class FutureCoinPairData
{
    private $allow_vars_value = ['status', 'slippage_percent', 'maker_fees_percent', 'taker_fees_percent'];
    public float $maker_fees_percent = 0;
    public float $taker_fees_percent = 0;
    public float $slippage_percent = 0;
    public float $min_amount = 0;
    public float $max_amount = 0;
    public float $min_stop_limit_percent = 0;
    public float $max_stop_limit_percent = 0;
    public float $floor_ratio = 0;
    public float $cap_ratio = 0;
    public int $leverage = 0;
    public int $max_leverage = 0;
    public int $max_open_orders = 0;
    public float $funding_rate = 0;
    public string $funding_next_time = '';
    public string $code = '';
    public string $uid = '';
    public int $status = 1;
    public int $margin_mode = 0;
    public int $base_coin_id = 0;
    public int $trade_coin_id = 0;

    public function __construct(
        public int $collateral_type,
        public string $base_coin_code,
        public string $trade_coin_code,
        public int $base_decimal,
        public int $trade_decimal
    ) {}

    public function setProperty($name, $value, $force = false)
    {
        if(!property_exists($this, $name)) return;
        if($value || $force) $this->$name = $value;
    }

    public function toArray()
    {
        $all = get_object_vars($this);
        $newData = [];
        foreach ($all as $key => $value) {
            // Allow value will be set in array
            if(in_array($key, $this->allow_vars_value))
                $newData[$key] = $value;

            // Filter Values And Make New Array
            elseif(!$value) continue;
                $newData[$key] = $value;
        }
        return $newData;
    }
}
