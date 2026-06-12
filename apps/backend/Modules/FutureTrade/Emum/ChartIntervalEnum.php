<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;
use Modules\FutureTrade\Entities\Charts\FutureFifteenMinuteChart;
use Modules\FutureTrade\Entities\Charts\FutureFiveMinuteChart;
use Modules\FutureTrade\Entities\Charts\FutureFourHourChart;
use Modules\FutureTrade\Entities\Charts\FutureOneDayChart;
use Modules\FutureTrade\Entities\Charts\FutureThirtyMinuteChart;
use Modules\FutureTrade\Entities\Charts\FutureTwoHourChart;

enum ChartIntervalEnum: int
{
    use RenderTrait;
    case FiveMinuteChart = 5;
    case FifteenMinuteChart = 15;
    case ThirtyMinuteChart = 30;
    case TwoHourChart = 120;
    case FourHourChart = 240;
    case OneDayChart = 1440;

    /**
     * Get Status Label
     */
    public function label(): string
    {
        return match ($this) {
            self::FiveMinuteChart => __('5m'),
            self::FifteenMinuteChart => __('15m'),
            self::ThirtyMinuteChart => __('30m'),
            self::TwoHourChart => __('2h'),
            self::FourHourChart => __('4h'),
            self::OneDayChart => __('1d'),
        };
    }

    public function getModel()
    {
        return match ($this) {
            self::FiveMinuteChart => FutureFiveMinuteChart::class,
            self::FifteenMinuteChart => FutureFifteenMinuteChart::class,
            self::ThirtyMinuteChart => FutureThirtyMinuteChart::class,
            self::TwoHourChart => FutureTwoHourChart::class,
            self::FourHourChart => FutureFourHourChart::class,
            self::OneDayChart => FutureOneDayChart::class,
        };
    }
}
