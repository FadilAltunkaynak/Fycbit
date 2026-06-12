<?php

namespace Modules\FutureTrade\Services\MathService;

class MarketMath
{
    /**
     * Get Margin
     *
     * @return string
     */
    public static function margin(mixed $price, mixed $amount, mixed $leverage, int $scale = 8)
    {
        // Margin =  (Price * Amount) / Leverage
        $totalPrice = bcmulx($price, $amount, $scale);

        return bcdivx($totalPrice, $leverage, $scale);
    }
}
