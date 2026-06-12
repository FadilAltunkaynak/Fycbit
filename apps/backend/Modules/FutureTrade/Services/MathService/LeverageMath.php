<?php

namespace Modules\FutureTrade\Services\MathService;

class LeverageMath
{
    /**
     * Get Maximum Maintenance Margin Amount
     *
     * @param  mixed  $position_amount  Position Size As Trade Coin Amount
     * @param  mixed  $maintenance_margin_rate  Maintenance Margin Rate
     */
    public static function initial_margin(mixed $price, mixed $amount, mixed $leverage, $scale = 8): string
    {
        $total_price = bcmulx($price, $amount, $scale);
        return bcdivx($total_price, $leverage, $scale);
    }

    /**
     * Get Maximum Maintenance Margin Amount
     *
     * @param  mixed  $position_amount  Position Size As Trade Coin Amount
     * @param  mixed  $maintenance_margin_rate  Maintenance Margin Rate
     */
    public static function max_maintenance_amount(mixed $position_amount, mixed $maintenance_margin_rate, $scale = 8): string
    {
        $maintenance_margin_rate = bcdivx($maintenance_margin_rate, 100, $scale);

        return bcmulx($position_amount, $maintenance_margin_rate, $scale);
    }
}
