<?php

namespace Modules\FutureTrade\Services\OrderService\Traits;

use App\Facades\ResponseFacade;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\OrderType;

trait StopLimitValidation
{
    /**
     * Validate stop limit Order Price
     * Should not less than min stop limit and not greater than max stop limit
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkStopLimitValidation(FutureOrderData $orderData): bool
    {
        return $this->checkMinStopLimitPriceValidation($orderData)
            && $this->checkMaxStopLimitPriceValidation($orderData)
            && $this->checkStopLimit($orderData);
    }

    /**
     * Validate min stop limit Order Price
     * Should not less than min stop limit
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkStopLimit(FutureOrderData $orderData): bool
    {

        if (
            $orderData->order_type == OrderType::BUY &&
            ! ($orderData->price > $orderData->stop_price)
        ) {
            ResponseFacade::failed(
                messageOrData: __('Stop price should be less than :price', ['price' => $orderData->price])
            )->safeThrow();
        }

        if (
            $orderData->order_type == OrderType::SELL &&
            ! ($orderData->price < $orderData->stop_price)
        ) {
            ResponseFacade::failed(
                messageOrData: __('Stop price should be greater than :price', ['price' => $orderData->price])
            )->safeThrow();
        }

        return true;
    }

    /**
     * Validate min stop limit Order Price
     * Should not less than min stop limit
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMinStopLimitPriceValidation(FutureOrderData $orderData): bool
    {
        if (! $orderData->coinPair->min_stop_limit_percent) {
            return true;
        }

        $percent = bcdivx($orderData->coinPair->min_stop_limit_percent, 100);
        $markPercent = bcmulx($orderData->market_price, $percent);
        $minPrice = bcsubx($orderData->market_price, $markPercent);

        if ($minPrice > $orderData->price) {
            ResponseFacade::failed(
                messageOrData: __('Price should be greater than :price',
                    replace: ['price' => $minPrice])
            )->safeThrow();
        }

        return true;
    }

    /**
     * Validate max stop limit Order Price
     * Should not greater than max stop limit
     *
     * use rescue() to avoid exception
     *
     * @throws \Exception
     */
    public function checkMaxStopLimitPriceValidation(FutureOrderData $orderData): bool
    {
        if (! $orderData->coinPair->max_stop_limit_percent) {
            return true;
        }

        $percent = bcdivx($orderData->coinPair->max_stop_limit_percent, 100);
        $markPercent = bcmulx($orderData->market_price, $percent);
        $maxPrice = bcaddx($orderData->market_price, $markPercent);

        if ($maxPrice < $orderData->price) {
            ResponseFacade::failed(
                messageOrData: __('Price should be less than :price',
                    replace: ['price' => $maxPrice])
            )->safeThrow();
        }

        return true;
    }
}
