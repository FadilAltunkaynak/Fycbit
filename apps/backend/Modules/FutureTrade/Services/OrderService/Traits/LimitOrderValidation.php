<?php

namespace Modules\FutureTrade\Services\OrderService\Traits;

use App\Facades\ResponseFacade;
use Modules\FutureTrade\Entities\FutureCoinPair;

trait LimitOrderValidation
{

    /**
     * Limit Order Validation
     * @param FutureCoinPair $coin_pair
     * @param float $mark_price
     * @param float $price
     * 
     * @throws \Exception 
     * use rescue() to avoid exception
     * @return bool
     */
    public function checkLimitValidation(FutureCoinPair $coin_pair, float $mark_price, float $price): bool
    {
        return $this->checkCapAndFloorPrice($coin_pair, $mark_price, $price) &&
               $this->checkMinAndMaxOrderPrice($coin_pair, $mark_price, $price);
    }

    /**
     * Validate cap and floor price
     * 
     * @param FutureCoinPair $coin_pair
     * @param float $mark_price
     * @param float $price
     * @return bool
     * 
     * @throws \Exception 
     * use rescue() to avoid exception
     */
    public function checkCapAndFloorPrice(FutureCoinPair $coin_pair, float $mark_price, float $price): bool
    {
        return $this->checkCapPrice  ($coin_pair, $mark_price, $price)
            && $this->checkFloorPrice($coin_pair, $mark_price, $price);
    }

    /**
     * Validate cap price
     * 
     * @param FutureCoinPair $coin_pair
     * @param float $mark_price
     * @param float $price
     * @return bool
     * 
     * @throws \Exception 
     * use rescue() to avoid exception
     */
    public function checkCapPrice(FutureCoinPair $coin_pair, float $mark_price, float $price): bool
    {
        $percent  = bcdivx($coin_pair->cap_ratio, 100);
        $percent  = bcaddx(1, $percent);
        $capPrice = bcmulx($mark_price, $percent);

        if($capPrice < $price)
            ResponseFacade::failed(
                messageOrData: __('Price should be less than :price price',
                replace: ['price' => $capPrice])
            )->safeThrow();

        return true;
    }

    /**
     * Validate floor price
     * 
     * @param FutureCoinPair $coin_pair
     * @param float $mark_price
     * @param float $price
     * @return bool
     * 
     * @throws \Exception 
     * use rescue() to avoid exception
     */
    public function checkFloorPrice(FutureCoinPair $coin_pair, float $mark_price, float $price): bool
    {
        $percent    = bcdivx($coin_pair->floor_ratio, 100);
        $percent    = bcsubx(1, $percent);
        $floorPrice = bcmulx($mark_price, $percent);

        if($floorPrice > $price)
            ResponseFacade::failed(
                messageOrData: __('Price should be greater than :price price',
                replace: ['price' => $floorPrice])
            )->safeThrow();

        return true;
    }
}