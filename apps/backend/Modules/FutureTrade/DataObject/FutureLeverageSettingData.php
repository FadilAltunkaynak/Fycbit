<?php

namespace Modules\FutureTrade\DataObject;

class FutureLeverageSettingData
{
    private $allow_vars_value = [];
    public string $uid = '';


    public function __construct(
        public string $coin_pair_uid,
        public float $min_position_amount,
        public float $max_position_amount,
        public float $max_leverage,
        public float $maintenance_margin_rate,
        public float $maintenance_amount
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