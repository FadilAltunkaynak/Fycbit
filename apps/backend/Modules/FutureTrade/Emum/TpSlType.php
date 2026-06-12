<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum TpSlType: int
{
    use RenderTrait;
    case NONE = 0;
    case TAKE_PROFIT = 1;
    case STOP_LOSS = 2;

    /**
     * Get Status Label
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => __('None'),
            self::TAKE_PROFIT => __('Take Profit'),
            self::STOP_LOSS => __('Stop Loss'),
        };
    }
}
